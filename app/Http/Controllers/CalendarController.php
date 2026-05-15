<?php

namespace App\Http\Controllers;

use App\Models\OfficeDateAvailability;
use App\Models\AppointmentSlot;
use App\Models\LawOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    private const MAX_SLOT_CAPACITY = 4;

    private function resolveLawOfficeIdForLawyer($user)
    {
        if (!$user || $user->role !== 'lawyer') {
            return null;
        }

        if ($user->law_office_id) {
            return $user->law_office_id;
        }

        if (!empty($user->law_office)) {
            $office = LawOffice::where('law_office', $user->law_office)->first();
            if ($office) {
                return $office->id;
            }
        }

        return null;
    }

    public function saveMonthColor(Request $request, $officeId = null)
    {
        \Log::info('CalendarController: saveMonthColor()', $request->all());

        try {
            $request->validate([
                'date'  => 'required|date',
                'color' => 'required|in:red,yellow,green',
                'description' => 'nullable|string|max:500'
            ]);

            $user = Auth::user();
            
            // If user is a lawyer, force use of their office
            if ($user && $user->role === 'lawyer') {
                $lawyerOfficeId = $this->resolveLawOfficeIdForLawyer($user);
                if (!$lawyerOfficeId) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No law office assigned to this user'
                    ], 403);
                }
                $officeId = $lawyerOfficeId;
            } else {
                $officeId = $officeId ?? session('law_office_id');
            }

            OfficeDateAvailability::updateOrCreate(
                ['law_office_id' => $officeId, 'date' => $request->date],
                [
                    'color' => $request->color,
                    'description' => $request->description ?? '',
                ]
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Date availability saved'
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
    public function saveWeekColor(Request $request, $officeId = null)
    {
        \Log::info('CalendarController: saveWeekColor()', $request->all());

        try {
            $request->validate([
                'date'  => 'required|date',
                'time_range'  => 'required|string',
                'capacity_remaining' => 'required|integer|min:0',
            ]);

            $user = Auth::user();

            // If user is a lawyer, force use of their office
            if ($user && $user->role === 'lawyer') {
                $lawyerOfficeId = $this->resolveLawOfficeIdForLawyer($user);
                if (!$lawyerOfficeId) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No law office assigned to this user'
                    ], 403);
                }
                $officeId = $lawyerOfficeId;
            } else {
                $officeId = $officeId ?? session('law_office_id');
            }

            AppointmentSlot::updateOrCreate(
                ['law_office_id' => $officeId, 'date' => $request->date, 'time_range' => $request->time_range],
                ['capacity_remaining' => $request->capacity_remaining]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Slot saved'
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
            $user = Auth::user();

            \Log::info("Loading month colors for: {$month}", ['user' => $user?->id, 'role' => $user?->role]);

            // If user is a lawyer, only show their own office's calendar
            $lawOfficeId = null;
            if ($user && $user->role === 'lawyer') {
                $lawOfficeId = $this->resolveLawOfficeIdForLawyer($user);
                if (!$lawOfficeId) {
                    \Log::warning("Lawyer user has no law_office_id assigned", ['user_id' => $user->id]);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No law office assigned to this user'
                    ], 403);
                }
                \Log::info("Lawyer accessing calendar for office: {$lawOfficeId}");
            } else if ($request->has('office_id')) {
                // Admin can specify an office
                $lawOfficeId = $request->office_id;
            }

            // Get date-level colors from month_colors table - NOW FILTERED BY LAW OFFICE ID
            $query = DB::table('month_colors')
                ->where('month', $month)
                ->where('law_office_id', $lawOfficeId)
                ->whereNotNull('date_color')
                ->select('date', 'date_color', 'date_description');

            $colors = $query->get()
                ->mapWithKeys(function ($row) {
                    return [$row->date => [
                        'color' => $row->date_color,
                        'description' => $row->date_description
                    ]];
                });

            \Log::info("Month colors loaded:", [
                'month' => $month,
                'law_office_id' => $lawOfficeId,
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

            $user = Auth::user();
            $lawOfficeId = null;

            // If user is a lawyer, only show their own office's data
            if ($user && $user->role === 'lawyer') {
                $lawOfficeId = $this->resolveLawOfficeIdForLawyer($user);
                if (!$lawOfficeId) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No law office assigned to this user'
                    ], 403);
                }
            } else if ($request->has('office_id')) {
                $lawOfficeId = $request->office_id;
            }

            \Log::info('Week data loading for range:', [
                'requested_date' => $request->date,
                'start_of_week' => $start->toDateString(),
                'end_of_week' => $end->toDateString(),
                'law_office_id' => $lawOfficeId
            ]);

            // Month-level colors from month_colors table - NOW FILTERED BY LAW OFFICE ID
            $monthColorsQuery = DB::table('month_colors')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->where('law_office_id', $lawOfficeId)
                ->whereNotNull('date_color')
                ->select('date', 'date_color', 'date_description');

            $monthColors = $monthColorsQuery->get()
                ->mapWithKeys(function ($row) {
                    return [$row->date => [
                        'color' => $row->date_color,
                        'description' => $row->date_description
                    ]];
                });

            // Week-level colors from week_colors table - NOW FILTERED BY LAW OFFICE ID
            $weekColorsQuery = DB::table('week_colors')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->where('law_office_id', $lawOfficeId)
                ->select('date', 'time', 'color', 'description', 'booked', 'time_slot', 'slot_number');

            $weekColors = $weekColorsQuery->get()
                ->groupBy('date')
                ->map(function ($slots) {
                    return $slots->mapWithKeys(function ($slot) {
                        return [$slot->time => [
                            'color'       => $slot->color,
                            // Force description to show the slot_number (available slots)
                            'description' => 'Available slots: ' . ((int) ($slot->slot_number ?? 0)),
                            'booked'      => $slot->booked,
                            'slot_number' => (int) ($slot->slot_number ?? 0),
                            'time_slot'   => $slot->time_slot // keep if other code expects it
                        ]];
                    });
                });


            \Log::info('Week data loaded:', [
                'date_range' => $start->toDateString() . ' to ' . $end->toDateString(),
                'law_office_id' => $lawOfficeId,
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
            $user = Auth::user();
            $lawOfficeId = null;

            // If user is a lawyer, only show their own office's data
            if ($user && $user->role === 'lawyer') {
                $lawOfficeId = $this->resolveLawOfficeIdForLawyer($user);
                if (!$lawOfficeId) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No law office assigned to this user'
                    ], 403);
                }
            } else if ($request->has('office_id')) {
                $lawOfficeId = $request->office_id;
            }

            \Log::info("Loading date data for: {$date}", ['law_office_id' => $lawOfficeId]);

            // Get date-level data from month_colors - NOW FILTERED BY LAW OFFICE ID
            $dateData = DB::table('month_colors')
                ->where('date', $date)
                ->where('law_office_id', $lawOfficeId)
                ->select('date_color', 'date_description')
                ->first();

            // Get time slot data from week_colors - NOW FILTERED BY LAW OFFICE ID
            $weekSlots = DB::table('week_colors')
                ->where('date', $date)
                ->where('law_office_id', $lawOfficeId)
                ->get()
                ->mapWithKeys(function ($row) {
                    return [
                        (int) $row->time_slot => [
                            'time_slot'   => (int) $row->time_slot,
                            'slot_number' => (int) ($row->slot_number ?? 0),
                            'color'       => $row->color,
                            'description' => 'Available slots: ' . (int) ($row->slot_number ?? 0),
                            'time'        => $row->time,
                        ]
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'date_color' => $dateData->date_color ?? null,
                    'date_description' => $dateData->date_description ?? null,
                    'time_slots' => $weekSlots
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
            $user = Auth::user();
            
            \Log::info('User info', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_law_office_id' => $user->law_office_id,
                'request_office_id' => $request->office_id,
                'session_law_office_id' => session('law_office_id')
            ]);
            
            $lawOfficeId = null;

            // If user is a lawyer, force use of their office
            if ($user && $user->role === 'lawyer') {
                $lawOfficeId = $this->resolveLawOfficeIdForLawyer($user);
                if (!$lawOfficeId) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No law office assigned to this user'
                    ], 403);
                }
            } else if ($request->has('office_id') && $request->office_id) {
                $lawOfficeId = $request->office_id;
            } else {
                $lawOfficeId = session('law_office_id');
                // For administrators, if no office is selected, return an error asking them to select an office
                if (!$lawOfficeId && $user && in_array($user->role, ['admin', 'superadmin'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Please select a law office from the dropdown before setting availability'
                    ], 400);
                }
            }

            \Log::info("Processing date: {$date}, month: {$month}", ['law_office_id' => $lawOfficeId]);

            DB::beginTransaction();

            // 1. Handle DATE-LEVEL data in month_colors table
            if ($request->date_color) {
                \Log::info("Saving date-level data to month_colors: {$request->date_color}");
                
                DB::table('month_colors')->updateOrInsert(
                    ['date' => $date, 'law_office_id' => $lawOfficeId],
                    [
                        'month' => $month,
                        'date_color' => $request->date_color,
                        'date_description' => $request->date_description ?? '',
                        'booked' => 0,
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
            } else {
                // Remove date-level record if no color - FILTERED BY LAW OFFICE ID
                DB::table('month_colors')
                    ->where('date', $date)
                    ->where('law_office_id', $lawOfficeId)
                    ->delete();
            }

            // 2. Handle TIME-SLOT data in week_colors table
            if ($request->time_slots && is_array($request->time_slots)) {
                foreach ($request->time_slots as $slot) {
                    $timeSlot = intval($slot['time_slot'] ?? 0);
                    $slotNumber = $this->normalizeSlotCapacity($slot['slot_number'] ?? 0);
                    $color = $slot['color'] ?? null;

                    // Validate slot number is within range (1-9)
                    if ($timeSlot < 1 || $timeSlot > 9) {
                        \Log::warning("Invalid time slot number: {$timeSlot}, skipping.");
                        continue;
                    }

                    // Skip if no valid color or slot number
                    if (!$color || $slotNumber <= 0) {
                        continue;
                    }

                    $timeRange = $this->getTimeRangeBySlot($timeSlot);

                    DB::table('week_colors')->updateOrInsert(
                        [
                            'date' => $date,
                            'time_slot' => $timeSlot,
                            'law_office_id' => $lawOfficeId
                        ],
                        [
                            'time' => $timeRange,
                            'slot_number' => $slotNumber,
                            'color' => $color,
                            'description' => $slot['description'] ?? '',
                            'booked' => 0,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }

            DB::commit();

            \Log::info('Calendar data saved successfully', [
                'date' => $date,
                'law_office_id' => $lawOfficeId,
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

    private function mapTimeSlot(int $timeSlot): string
    {
        $timeMap = [
            1 => '8:00 AM - 9:00 AM',
            2 => '9:00 AM - 10:00 AM',
            3 => '10:00 AM - 11:00 AM',
            4 => '11:00 AM - 12:00 PM',
            5 => '12:00 PM - 1:00 PM',
            6 => '1:00 PM - 2:00 PM',
            7 => '2:00 PM - 3:00 PM',
            8 => '3:00 PM - 4:00 PM',
            9 => '4:00 PM - 5:00 PM',
        ];

        return $timeMap[$timeSlot] ?? 'Unknown';
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

    private function normalizeSlotCapacity($value): int
    {
        return max(0, min(self::MAX_SLOT_CAPACITY, (int) $value));
    }
}
