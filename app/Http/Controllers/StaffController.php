<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffController extends Controller
{
    private const MAX_SLOT_CAPACITY = 4;

    /**
     * Get month colors for staff calendar
     */
    public function getMonthColors(Request $request)
    {
        try {
            $request->validate([
                'month' => 'required|string|size:7' // YYYY-MM
            ]);

            $month = $request->month;
            $user = Auth::user();
            $lawOfficeId = $user?->law_office_id;

            // Get date-level colors from month_colors table - FILTERED BY LAW OFFICE
            $colors = DB::table('month_colors')
                ->where('month', $month)
                ->where('law_office_id', $lawOfficeId)
                ->select('date', 'date_color as color', 'date_description as description')
                ->get()
                ->mapWithKeys(function ($row) {
                    return [$row->date => [
                        'color' => $row->color,
                        'description' => $row->description
                    ]];
                });

            return response()->json([
                'status' => 'success',
                'data' => $colors
            ]);

        } catch (\Exception $e) {
            \Log::error('Staff Calendar - getMonthColors ERROR: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load month colors'
            ], 500);
        }
    }

    /**
     * Get date data for modal
     */
    public function getDateData(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date'
            ]);

            $date = $request->date;
            $user = Auth::user();
            $lawOfficeId = $user?->law_office_id;

            // Get date-level data from month_colors - FILTERED BY LAW OFFICE
            $dateData = DB::table('month_colors')
                ->where('date', $date)
                ->where('law_office_id', $lawOfficeId)
                ->select('date_color', 'date_description')
                ->first();

            // Get time slot data from week_colors - FILTERED BY LAW OFFICE
            $timeSlots = DB::table('week_colors')
                ->where('date', $date)
                ->where('law_office_id', $lawOfficeId)
                ->get()
                ->mapWithKeys(function ($row) {
                    return [
                        $row->time_slot => [
                            'time_slot' => $row->time_slot,
                            'slot_number' => $row->slot_number,
                            'color' => $row->color,
                            'description' => $row->description
                        ]
                    ];
                })
                ->toArray();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'date_color' => $dateData->date_color ?? null,
                    'date_description' => $dateData->date_description ?? null,
                    'time_slots' => $timeSlots
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Staff Calendar - getDateData ERROR: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load date data'
            ], 500);
        }
    }

    /**
     * Save date data from modal
     */
    public function saveDateData(Request $request)
    {
        try {
            \Log::info('StaffController::saveDateData() - Request received', $request->all());
            
            $request->validate([
                'date' => 'required|date',
                'date_color' => 'nullable|in:red,orange,green',
                'date_description' => 'nullable|string|max:500',
                'time_slots' => 'nullable|array'
            ]);

            $date = $request->date;
            $month = substr($date, 0, 7);
            $user = Auth::user();
            $lawOfficeId = $user?->law_office_id;

            \Log::info('StaffController::saveDateData() - Processing date', [
                'date' => $date,
                'month' => $month,
                'law_office_id' => $lawOfficeId,
                'date_color' => $request->date_color,
                'time_slots_count' => $request->time_slots ? count($request->time_slots) : 0
            ]);

            DB::beginTransaction();

            // 1. Handle DATE-LEVEL data in month_colors table - INCLUDE LAW OFFICE ID
            if ($request->date_color) {
                DB::table('month_colors')->updateOrInsert(
                    ['date' => $date, 'law_office_id' => $lawOfficeId],
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
                    ->where('law_office_id', $lawOfficeId)
                    ->delete();
            }

            // 2. Handle TIME-SLOT data in week_colors table - INCLUDE LAW OFFICE ID
            if ($request->time_slots && is_array($request->time_slots)) {
                foreach ($request->time_slots as $slot) {
                    if (isset($slot['time_slot']) && isset($slot['slot_number']) && isset($slot['color'])) {
                        $timeSlot = (int)$slot['time_slot'];
                        $slotNumber = $this->normalizeSlotCapacity($slot['slot_number'] ?? 0);
                        $color = $slot['color'];
                        $description = $slot['description'] ?? null;
                        
                        // Map time slot to time range
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
                                'description' => $description,
                                'booked' => 0,
                                'updated_at' => now(),
                                'created_at' => DB::raw('IFNULL(created_at, NOW())')
                            ]
                        );
                    } else {
                        // If required data is missing, delete the record
                        DB::table('week_colors')
                            ->where('date', $date)
                            ->where('time_slot', $slot['time_slot'] ?? null)
                            ->where('law_office_id', $lawOfficeId)
                            ->delete();
                    }
                }
            }

            DB::commit();

            \Log::info('StaffController::saveDateData() - Data saved successfully', [
                'date' => $date,
                'law_office_id' => $lawOfficeId,
                'month_colors_updated' => !empty($request->date_color),
                'week_colors_count' => $request->time_slots ? count($request->time_slots) : 0
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Calendar data saved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Staff Calendar - saveDateData ERROR: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get time range by slot number
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

    private function normalizeSlotCapacity($value): int
    {
        return max(0, min(self::MAX_SLOT_CAPACITY, (int) $value));
    }

    /**
     * Staff dashboard view
     */
    public function index()
    {
return view('diffun_staff.staff');

    }

    /**
     * Staff walk-in logs view
     */
    public function walkinLogs()
    {
        return view('staff.walkInsLogs');
    }

    /**
     * Staff feedback reports view
     */
    public function feedbackReports()
    {
        return view('diffun_staff.feedbackReports');
    }

    /**
     * Staff account settings view
     */
    public function accountSettings()
    {
        return view('staff.staffAccountSetting');
    }
}
