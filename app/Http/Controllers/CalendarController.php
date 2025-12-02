<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /*
|--------------------------------------------------------------------------
| SAVE MONTH COLOR
|--------------------------------------------------------------------------
| Fixed: Now includes description field
*/
public function saveMonthColor(Request $request)
{
    \Log::info('CalendarController: saveMonthColor()', $request->all());

    try {
        $request->validate([
            'date'  => 'required|date',
            'color' => 'required|in:red,orange,green',
            'description' => 'nullable|string|max:500'
        ]);

        DB::table('month_colors')->updateOrInsert(
            ['date' => $request->date],
            [
                'month'      => substr($request->date, 0, 7),
                'color'      => $request->color,
                'description' => $request->description ?? '',
                'booked'     => false,
                'updated_at' => now(),
                'created_at' => now()
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Month color saved'
        ]);

    } catch (\Exception $e) {
        \Log::error('saveMonthColor ERROR: ' . $e->getMessage());
        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to save: ' . $e->getMessage()
        ], 500);
    }
}

/*
|--------------------------------------------------------------------------
| SAVE WEEK COLOR
|--------------------------------------------------------------------------
| Fixed: Now includes description field
*/
public function saveWeekColor(Request $request)
{
    \Log::info('CalendarController: saveWeekColor()', $request->all());

    try {
        $request->validate([
            'date'  => 'required|date',
            'time'  => 'required|string',
            'color' => 'required|in:red,orange,green',
            'description' => 'nullable|string|max:500',
            'time_slot' => 'nullable|integer|min:0' // Added time_slot validation
        ]);

        // Use the formatted time range directly (e.g., "8:00 AM - 9:00 AM")
        $timeRange = $request->time;

        // Set default time_slot based on color
        $timeSlotValue = $request->time_slot;
        if ($timeSlotValue === null) {
            $timeSlotValue = ($request->color === 'green') ? 3 : 0; // Default to 3 for green, 0 for others
        }

        // Automatically set booked based on time_slot
        $bookedValue = ($timeSlotValue > 0) ? 0 : 1;

        $existing = DB::table('week_colors')
            ->where('date', $request->date)
            ->where('time', $timeRange)
            ->first();

        if ($existing) {
            DB::table('week_colors')
                ->where('id', $existing->id)
                ->update([
                    'time'       => $timeRange,
                    'color'      => $request->color,
                    'description' => $request->description ?? '',
                    'time_slot'  => $timeSlotValue, // Added time_slot
                    'booked'     => $bookedValue,
                    'updated_at' => now()
                ]);
        } else {
            DB::table('week_colors')->insert([
                'date'       => $request->date,
                'time'       => $timeRange,
                'color'      => $request->color,
                'description' => $request->description ?? '',
                'time_slot'  => $timeSlotValue, // Added time_slot
                'booked'     => $bookedValue,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Week color saved'
        ]);

    } catch (\Exception $e) {
        \Log::error('saveWeekColor ERROR: ' . $e->getMessage());
        return response()->json([
            'status'  => 'error',
            'message' => 'Failed: ' . $e->getMessage()
        ], 500);
    }
}
/*
|--------------------------------------------------------------------------
| GET MONTH COLORS
|--------------------------------------------------------------------------
| FIXED: Now returns description
*/
public function getMonthColors(Request $request)
{
    try {
        $request->validate([
            'month' => 'required|string|size:7' // YYYY-MM
        ]);

        $month = $request->month;
        \Log::info("Loading month colors for: {$month}");

        // Get date-level colors from month_colors table
        $colors = DB::table('month_colors')
            ->where('month', $month)
            ->whereNotNull('date_color')
            ->select('date', 'date_color', 'date_description')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row->date => [
                    'color' => $row->date_color, // This is the key the frontend expects
                    'description' => $row->date_description
                ]];
            });

        \Log::info("Month colors loaded:", [
            'month' => $month,
            'colors_count' => $colors->count()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $colors
        ]);

    } catch (\Exception $e) {
        \Log::error('getMonthColors ERROR: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to load month colors: ' . $e->getMessage()
        ], 500);
    }
}

/*
|--------------------------------------------------------------------------
| LOAD WEEK DATA
|--------------------------------------------------------------------------
| FIXED: Now returns description for week colors
*/
public function loadWeekData(Request $request)
{
    try {
        $request->validate(['date' => 'required|date']);

        $date = Carbon::parse($request->date);
        
        // Use Sunday as start of week
        $start = $date->copy()->startOfWeek(Carbon::SUNDAY);
        $end   = $date->copy()->endOfWeek(Carbon::SATURDAY);

        \Log::info('Week data loading for range:', [
            'requested_date' => $request->date,
            'start_of_week' => $start->toDateString(),
            'end_of_week' => $end->toDateString()
        ]);

        // Month-level colors from month_colors table
        $monthColors = DB::table('month_colors')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('date_color')
            ->select('date', 'date_color', 'date_description')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row->date => [
                    'color' => $row->date_color,
                    'description' => $row->date_description
                ]];
            });

        // Week-level colors from week_colors table - FIXED: Include time_slot
        $weekColors = DB::table('week_colors')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->select('date', 'time', 'color', 'description', 'booked', 'time_slot') // Added time_slot
            ->get()
            ->groupBy('date')
            ->map(function ($slots) {
                return $slots->mapWithKeys(function ($slot) {
                    return [$slot->time => [
                        'color' => $slot->color,
                        'description' => $slot->description,
                        'booked' => $slot->booked,
                        'time_slot' => $slot->time_slot // Added time_slot
                    ]];
                });
            });

        \Log::info('Week data loaded:', [
            'date_range' => $start->toDateString() . ' to ' . $end->toDateString(),
            'month_colors_count' => $monthColors->count(),
            'week_colors_count' => $weekColors->count()
        ]);

        return response()->json([
            'status'       => 'success',
            'month_colors' => $monthColors,
            'week_colors'  => $weekColors
        ]);

    } catch (\Exception $e) {
        \Log::error('loadWeekData ERROR: ' . $e->getMessage());
        return response()->json([
            'status'  => 'error',
            'message' => 'Failed: ' . $e->getMessage()
        ], 500);
    }
}
/*
|--------------------------------------------------------------------------
| GET DATE DATA FOR MODAL
|--------------------------------------------------------------------------
*/
public function getDateData(Request $request)
{
    try {
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = $request->date;

        \Log::info("Loading date data for: {$date}");

        // Get date-level data from month_colors
        $dateData = DB::table('month_colors')
            ->where('date', $date)
            ->select('date_color', 'date_description')
            ->first();

        // Get time slot data from week_colors
        $timeSlots = DB::table('week_colors')
            ->where('date', $date)
            ->select('time_slot', 'color', 'description')
            ->get()
            ->keyBy('time_slot')
            ->map(function ($item) {
                return [
                    'color' => $item->color,
                    'description' => $item->description
                ];
            })
            ->toArray();

        \Log::info("Date data loaded:", [
            'date_color' => $dateData->date_color ?? null,
            'time_slots_count' => count($timeSlots)
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'date_color' => $dateData->date_color ?? null,
                'date_description' => $dateData->date_description ?? null,
                'time_slots' => $timeSlots
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('getDateData ERROR: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to load date data: ' . $e->getMessage()
        ], 500);
    }
}
/*
|--------------------------------------------------------------------------
| SAVE DATE DATA FROM MODAL - COMPATIBLE VERSION
|--------------------------------------------------------------------------
*/
public function saveDateData(Request $request)
{
    \Log::info('CalendarController: saveDateData() - START', $request->all());

    try {
        $request->validate([
            'date' => 'required|date',
            'date_color' => 'nullable|in:red,orange,green',
            'date_description' => 'nullable|string|max:500',
            'time_slots' => 'nullable|array'
        ]);

        $date = $request->date;
        $month = substr($date, 0, 7);

        \Log::info("Processing date: {$date}, month: {$month}");

        DB::beginTransaction();

        // 1. Handle DATE-LEVEL data in month_colors table
        if ($request->date_color) {
            \Log::info("Saving date-level data to month_colors: {$request->date_color}");
            
            DB::table('month_colors')->updateOrInsert(
                ['date' => $date],
                [
                    'month' => $month,
                    'date_color' => $request->date_color,
                    'date_description' => $request->date_description ?? '',
                    'booked' => 0,
                    'updated_at' => now(),
                    'created_at' => DB::raw('IFNULL(created_at, NOW())')
                ]
            );
        } else {
            // Remove date-level record if no color
            DB::table('month_colors')
                ->where('date', $date)
                ->delete();
        }

        // 2. Handle TIME-SLOT data in week_colors table
        if ($request->time_slots) {
           foreach ($request->time_slots as $slotNumber => $slotData) {
            $timeSlot = (int)$slotNumber;
            
            // Map time slot number to time range string
            $timeRange = $this->getTimeRangeBySlot($timeSlot);
            
            if (isset($slotData['color'])) {
                \Log::info("Saving time slot {$timeSlot} to week_colors: {$slotData['color']}", [
                    'date' => $date,
                    'time_range' => $timeRange,
                    'description' => $slotData['description'] ?? '',
                    'time_slot' => $slotData['time_slot'] ?? null
                ]);
                
                // Set default time_slot based on color
                $timeSlotValue = $slotData['time_slot'] ?? (($slotData['color'] === 'green') ? 3 : 0);
                $bookedValue = ($timeSlotValue > 0) ? 0 : 1;
                
                // Save to week_colors table
                DB::table('week_colors')->updateOrInsert(
                    [
                        'date' => $date,
                        'time_slot' => $timeSlot
                    ],
                    [
                        'time' => $timeRange,
                        'color' => $slotData['color'],
                        'description' => $slotData['description'] ?? '',
                        'time_slot' => $timeSlotValue, // Store the actual slot count
                        'booked' => $bookedValue,
                        'updated_at' => now(),
                        'created_at' => DB::raw('IFNULL(created_at, NOW())')
                    ]
                );
            } else {
                // Remove time slot record if no color
                DB::table('week_colors')
                    ->where('date', $date)
                    ->where('time_slot', $timeSlot)
                    ->delete();
            }
        }
        }

        DB::commit();

        \Log::info('Calendar data saved successfully', [
            'date' => $date,
            'date_color' => $request->date_color,
            'time_slots_count' => $request->time_slots ? count($request->time_slots) : 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Calendar data saved successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('saveDateData ERROR: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to save: ' . $e->getMessage()
        ], 500);
    }
}

/*
|--------------------------------------------------------------------------
| HELPER: GET TIME RANGE BY SLOT NUMBER
|--------------------------------------------------------------------------
*/
private function getTimeRangeBySlot($slotNumber)
{
    $timeSlots = [
        1 => '8:00 AM - 9:00 AM',
        2 => '9:00 AM - 10:00 AM', 
        3 => '10:00 AM - 11:00 AM',
        4 => '11:00 AM - 12:00 PM',
        5 => '12:00 PM - 1:00 PM',
        6 => '1:00 PM - 2:00 PM',
        7 => '2:00 PM - 3:00 PM',
        8 => '3:00 PM - 4:00 PM',
        9 => '4:00 PM - 5:00 PM'
    ];

    return $timeSlots[$slotNumber] ?? 'Unknown Time Slot';
}
/*
|--------------------------------------------------------------------------
| FIX EXISTING WEEK COLORS DATA
|--------------------------------------------------------------------------
| This will add time_slot to existing records that don't have it
*/
public function fixExistingWeekColors()
{
    try {
        // Get all week colors without time_slot or with time_slot = 0 but color is green
        $recordsToUpdate = DB::table('week_colors')
            ->where(function($query) {
                $query->whereNull('time_slot')
                      ->orWhere('time_slot', 0);
            })
            ->where('color', 'green')
            ->get();

        $updatedCount = 0;

        foreach ($recordsToUpdate as $record) {
            DB::table('week_colors')
                ->where('id', $record->id)
                ->update([
                    'time_slot' => 3, // Set default slot count for existing green records
                    'booked' => 0,
                    'updated_at' => now()
                ]);
            $updatedCount++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "Updated {$updatedCount} records with time_slot",
            'updated_count' => $updatedCount
        ]);

    } catch (\Exception $e) {
        \Log::error('fixExistingWeekColors ERROR: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fix existing data: ' . $e->getMessage()
        ], 500);
    }
}
}
