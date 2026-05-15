<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CordonCalendarSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        DB::table('cordon_date_availabilities')->truncate();
        DB::table('cordon_time_slots')->truncate();
        
        // Generate data for next 3 months
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->addMonths(3)->endOfMonth();
        
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            
            // Insert date availability
            DB::table('cordon_date_availabilities')->insert([
                'date' => $dateStr,
                'availability_status' => 'available',
                'total_slots' => 9,
                'booked_slots' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Generate time slots
            $slots = [];
            for ($hour = 8; $hour < 17; $hour++) {
                $slotNumber = $hour - 7;
                $slots[] = [
                    'date' => $dateStr,
                    'slot_number' => $slotNumber,
                    'start_time' => sprintf('%02d:00:00', $hour),
                    'end_time' => sprintf('%02d:00:00', $hour + 1),
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            DB::table('cordon_time_slots')->insert($slots);
            
            $current->addDay();
        }
        
        $this->command->info('Cordon Branch calendar data seeded successfully!');
        $this->command->info('Total dates: ' . $startDate->diffInDays($endDate));
        $this->command->info('Total time slots: ' . ($startDate->diffInDays($endDate) * 9));
    }
}