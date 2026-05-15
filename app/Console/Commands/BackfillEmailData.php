<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillEmailData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:backfill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing message_id and receiver_id for email messages in chattbl';

    public function handle()
    {
        $this->info('Starting backfill of chattbl...');

        // 1) Populate receiver_id where possible
        $rows = DB::table('chattbl')
            ->whereNull('receiver_id')
            ->whereNotNull('receiver_email')
            ->get();

        $updatedReceiverCount = 0;
        foreach ($rows as $row) {
            $userId = DB::table('users')->where('email', $row->receiver_email)->value('id');
            if ($userId) {
                DB::table('chattbl')->where('id', $row->id)->update(['receiver_id' => $userId]);
                $updatedReceiverCount++;
            }
        }

        $this->info("Updated receiver_id for {$updatedReceiverCount} rows");

        // 2) Populate message_id where missing (if column exists)
        if (Schema::hasColumn('chattbl', 'message_id')) {
            $rows = DB::table('chattbl')
                ->whereNull('message_id')
                ->get();

            $updatedMessageIdCount = 0;
            foreach ($rows as $row) {
                // Create deterministic fallback to avoid collisions
                $seed = ($row->sender_email ?? '') . '|' . ($row->subject ?? '') . '|' . ($row->timestamp_normalized ?? $row->created_at ?? '') . '|' . $row->id;
                $messageId = md5($seed);
                DB::table('chattbl')->where('id', $row->id)->update(['message_id' => $messageId]);
                $updatedMessageIdCount++;
            }

            $this->info("Updated message_id for {$updatedMessageIdCount} rows");
        } else {
            $this->warn('Column message_id does not exist on chattbl, skipping message_id backfill');
        }

        $this->info('Backfill completed.');

        return 0;
    }
}
