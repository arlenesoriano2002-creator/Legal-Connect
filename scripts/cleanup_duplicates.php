<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$options = getopt('', ['apply']);
$apply = isset($options['apply']);

echo "cleanup_duplicates.php - Dry run by default. Use --apply to actually delete.\n";
if ($apply) echo "Running in APPLY mode: duplicates WILL be deleted.\n";

$res = [
    'by_message_id' => [
        'groups_found' => 0,
        'rows_marked_for_deletion' => 0,
        'example_groups' => []
    ],
    'by_signature' => [
        'groups_found' => 0,
        'rows_marked_for_deletion' => 0,
        'example_groups' => []
    ]
];

// Part 1: De-duplicate by non-null message_id
$dupIds = DB::table('chattbl')
    ->selectRaw('message_id, COUNT(*) as c')
    ->whereNotNull('message_id')
    ->groupBy('message_id')
    ->havingRaw('COUNT(*) > 1')
    ->pluck('c', 'message_id')
    ->toArray();

$res['by_message_id']['groups_found'] = count($dupIds);

$toDelete = [];
foreach ($dupIds as $messageId => $count) {
    // select rows for this message_id, order preferring sender_id IS NOT NULL (so 0 counts)
    $rows = DB::table('chattbl')
        ->where('message_id', $messageId)
        ->orderByRaw('CASE WHEN sender_id IS NULL THEN 1 ELSE 0 END ASC')
        ->orderBy('created_at', 'asc')
        ->get();

    if ($rows->count() <= 1) continue;

    // keep first, delete rest
    $keep = $rows->first();
    $others = $rows->slice(1);
    $ids = $others->pluck('id')->toArray();
    $res['by_message_id']['rows_marked_for_deletion'] += count($ids);
    if (count($res['by_message_id']['example_groups']) < 5) {
        $res['by_message_id']['example_groups'][] = [
            'message_id' => $messageId,
            'keep_id' => $keep->id,
            'delete_ids_sample' => $ids
        ];
    }
    $toDelete = array_merge($toDelete, $ids);
}

// Part 2: For rows with NULL message_id, dedupe by signature (sender|receiver|subject|message|timestamp_normalized)
$rowsNullMid = DB::table('chattbl')
    ->select('id', 'sender_email', 'receiver_email', 'subject', 'message', 'timestamp_normalized', 'sender_id', 'created_at')
    ->whereNull('message_id')
    ->get();

$groups = [];
foreach ($rowsNullMid as $r) {
    $sig = md5(strtolower(trim($r->sender_email)) . '|' . strtolower(trim($r->receiver_email)) . '|' . trim($r->subject) . '|' . trim($r->message) . '|' . ($r->timestamp_normalized ?? ''));
    if (!isset($groups[$sig])) $groups[$sig] = [];
    $groups[$sig][] = (array)$r;
}

foreach ($groups as $sig => $items) {
    if (count($items) <= 1) continue;
    // sort preferring sender_id IS NOT NULL first
    usort($items, function($a, $b) {
        $aScore = is_null($a['sender_id']) ? 1 : 0;
        $bScore = is_null($b['sender_id']) ? 1 : 0;
        if ($aScore !== $bScore) return $aScore - $bScore;
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });
    $keep = array_shift($items);
    $ids = array_map(function($x){ return $x['id']; }, $items);
    $res['by_signature']['groups_found']++;
    $res['by_signature']['rows_marked_for_deletion'] += count($ids);
    if (count($res['by_signature']['example_groups']) < 5) {
        $res['by_signature']['example_groups'][] = [
            'signature' => $sig,
            'keep_id' => $keep['id'],
            'delete_ids_sample' => $ids
        ];
    }
    $toDelete = array_merge($toDelete, $ids);
}

$res['planned_delete_count'] = count($toDelete);

// Show summary
echo "Duplicate groups by message_id: {$res['by_message_id']['groups_found']}, rows planned for deletion: {$res['by_message_id']['rows_marked_for_deletion']}\n";
echo "Duplicate groups by signature (null message_id): {$res['by_signature']['groups_found']}, rows planned for deletion: {$res['by_signature']['rows_marked_for_deletion']}\n";
echo "Total rows planned for deletion: {$res['planned_delete_count']}\n";

if (count($res['by_message_id']['example_groups'])) {
    echo "Examples (message_id groups):\n" . print_r($res['by_message_id']['example_groups'], true) . "\n";
}
if (count($res['by_signature']['example_groups'])) {
    echo "Examples (signature groups):\n" . print_r($res['by_signature']['example_groups'], true) . "\n";
}

if ($res['planned_delete_count'] === 0) {
    echo "Nothing to delete.\n";
    exit(0);
}

if (!$apply) {
    echo "Dry run complete. No rows were deleted. Re-run with --apply to delete the rows above.\n";
    exit(0);
}

// Apply deletions in transaction
try {
    DB::beginTransaction();
    $deleted = 0;
    // chunk deletes to be safe
    $chunks = array_chunk($toDelete, 1000);
    foreach ($chunks as $chunk) {
        $count = DB::table('chattbl')->whereIn('id', $chunk)->delete();
        $deleted += $count;
    }
    DB::commit();
    echo "Deleted {$deleted} rows.\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "Error during deletion: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Cleanup complete.\n";
