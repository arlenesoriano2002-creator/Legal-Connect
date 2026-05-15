<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Usage: php fuzzy_cleanup_scan.php [days]
$days = isset($argv[1]) ? intval($argv[1]) : 30;
echo "Fuzzy duplicate scan (last {$days} days) - dry run\n";

$cutoff = Carbon::now()->subDays($days)->format('Y-m-d H:i:s');

$rows = DB::table('chattbl')
    ->select('id','message_id','sender_email','receiver_email','subject','message','timestamp_normalized','created_at')
    ->where('created_at', '>=', $cutoff)
    ->orderBy('created_at','asc')
    ->get()
    ->toArray();

$total = count($rows);
echo "Rows inspected: {$total}\n";

// Normalize text helper
function norm_text($s) {
    if ($s === null) return '';
    $s = html_entity_decode($s);
    $s = strip_tags($s);
    $s = preg_replace('/\s+/', ' ', $s);
    $s = trim($s);
    return mb_strtolower($s);
}

// Build index by participant pair + date bucket (same day) to reduce comparisons
$buckets = [];
foreach ($rows as $r) {
    $s = norm_text($r->sender_email);
    $t = norm_text($r->receiver_email);
    $date = substr(($r->timestamp_normalized ?: $r->created_at), 0, 10); // YYYY-MM-DD
    if (empty($s) || empty($t)) continue;
    $key = $s . '|' . $t . '|' . $date;
    if (!isset($buckets[$key])) $buckets[$key] = [];
    $buckets[$key][] = $r;
}

$pairs = []; // store candidate pairs with scores
$pairCount = 0;
foreach ($buckets as $key => $list) {
    $n = count($list);
    if ($n <= 1) continue;
    // compare all pairs within bucket
    for ($i=0;$i<$n;$i++) {
        for ($j=$i+1;$j<$n;$j++) {
            $a = $list[$i];
            $b = $list[$j];
            // skip if exact same id
            if ($a->id == $b->id) continue;
            // compute time diff
            $t1 = strtotime($a->timestamp_normalized ?: $a->created_at);
            $t2 = strtotime($b->timestamp_normalized ?: $b->created_at);
            $timeDiff = abs($t1 - $t2);
            // only consider within 10 minutes (600s)
            if ($timeDiff > 600) continue;

            // if message_id non-null and equal, already exact dup - include anyway
            if (!empty($a->message_id) && $a->message_id === $b->message_id) {
                $score = 100;
            } else {
                // subject similarity
                $subA = norm_text($a->subject);
                $subB = norm_text($b->subject);
                $subSim = 0;
                if ($subA !== '' || $subB !== '') {
                    similar_text($subA, $subB, $subSim);
                }
                // message similarity via levenshtein normalized
                $msgA = norm_text(substr($a->message ?? '', 0, 2000));
                $msgB = norm_text(substr($b->message ?? '', 0, 2000));
                $msgSim = 0;
                if ($msgA === $msgB && $msgA !== '') {
                    $msgSim = 100;
                } else {
                    $maxL = max(mb_strlen($msgA), mb_strlen($msgB));
                    if ($maxL > 0) {
                        $lev = levenshtein($msgA, $msgB);
                        // avoid division by zero
                        $msgSim = max(0, (1 - ($lev / $maxL)) * 100);
                    }
                }
                // combined score: weighted (message 0.7, subject 0.3)
                $score = ($msgSim * 0.7) + ($subSim * 0.3);
            }

            // threshold: consider candidate if score >= 65 and timeDiff <= 600
            if ($score >= 65) {
                $pairs[] = [
                    'a_id' => $a->id,
                    'b_id' => $b->id,
                    'a_msgid' => $a->message_id,
                    'b_msgid' => $b->message_id,
                    'timeDiff' => $timeDiff,
                    'score' => round($score,2),
                    'a_created' => $a->created_at,
                    'b_created' => $b->created_at,
                    'a_subject' => $a->subject,
                    'b_subject' => $b->subject
                ];
                $pairCount++;
            }
        }
    }
}

echo "Candidate pairs found: {$pairCount}\n";

if ($pairCount === 0) {
    echo "No fuzzy duplicates detected in the scanned window.\n";
    exit(0);
}

// Merge pairs into groups (connected components)
$ids = [];
foreach ($pairs as $p) { $ids[$p['a_id']] = true; $ids[$p['b_id']] = true; }
$ids = array_keys($ids);
$parent = [];
foreach ($ids as $id) $parent[$id] = $id;
function findp(&$parent, $x) { return $parent[$x] === $x ? $x : ($parent[$x] = findp($parent, $parent[$x])); }
function unionp(&$parent, $a, $b) { $pa = findp($parent,$a); $pb = findp($parent,$b); if ($pa !== $pb) $parent[$pb] = $pa; }
foreach ($pairs as $p) unionp($parent, $p['a_id'], $p['b_id']);

$groups = [];
foreach ($ids as $id) {
    $root = findp($parent,$id);
    if (!isset($groups[$root])) $groups[$root] = [];
    $groups[$root][] = $id;
}

// Build detailed group info
$groupDetails = [];
foreach ($groups as $root => $members) {
    if (count($members) <= 1) continue;
    // fetch rows for members
    $rows = DB::table('chattbl')->whereIn('id', $members)->get()->keyBy('id')->toArray();
    $groupDetails[] = [
        'group_root' => $root,
        'count' => count($members),
        'members' => array_map(function($id) use ($rows){ $r = $rows[$id]; return ['id'=>$r->id,'message_id'=>$r->message_id,'created_at'=>$r->created_at,'subject'=>substr($r->subject,0,80)]; }, $members)
    ];
}

echo "Potential duplicate groups: " . count($groupDetails) . "\n";
foreach (array_slice($groupDetails,0,10) as $g) {
    echo "Group root: {$g['group_root']} (count={$g['count']})\n";
    foreach ($g['members'] as $m) {
        echo "  - id={$m['id']}, message_id=" . ($m['message_id'] ?? 'NULL') . ", created_at={$m['created_at']}, subject=" . trim(preg_replace('/\s+/', ' ', $m['subject'])) . "\n";
    }
    echo "\n";
}

// Summary of pairs (first 20)
echo "Sample candidate pairs (first 20):\n";
foreach (array_slice($pairs,0,20) as $p) {
    echo "a={$p['a_id']} b={$p['b_id']} score={$p['score']} timeDiff={$p['timeDiff']}s\n";
}

echo "Scan complete.\n";
