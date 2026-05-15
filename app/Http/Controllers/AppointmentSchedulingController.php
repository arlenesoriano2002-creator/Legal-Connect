<?php

namespace App\Http\Controllers;

use App\Models\AppointmentSlot;
use App\Models\OfficeDateAvailability;
use App\Models\LawOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentSchedulingController extends Controller
{
    /**
     * Display scheduling overview for all offices
     */
    public function index()
    {
        $offices = LawOffice::all();
        $schedulingData = [];

        foreach ($offices as $office) {
            $schedulingData[] = [
                'office' => $office,
                'slots_count' => AppointmentSlot::forOffice($office->id)->count(),
                'availabilities_count' => OfficeDateAvailability::where('law_office_id', $office->id)->count(),
            ];
        }

        return view('admin.scheduling.list', compact('schedulingData'));
    }

    /**
     * Display calendar view for a specific office
     */
    public function showCalendar($officeId)
    {
        $office = LawOffice::findOrFail($officeId);
        session(['law_office_id' => $officeId]);

        return view('admin.scheduling.calendar', compact('office'));
    }

    /**
     * Get all slots for an office (JSON response for AJAX)
     */
    public function getSlots($officeId)
    {
        $office = LawOffice::findOrFail($officeId);
        $slots = AppointmentSlot::forOffice($officeId)
            ->orderBy('date')
            ->orderBy('time_range')
            ->get();

        return response()->json([
            'status' => 'success',
            'office' => $office,
            'slots' => $slots,
        ]);
    }

    /**
     * Store a new appointment slot
     */
    public function storeSlot(Request $request, $officeId)
    {
        $request->validate([
            'date' => 'required|date',
            'time_range' => 'required|string',
            'capacity_remaining' => 'required|integer|min:0',
        ]);

        $slot = AppointmentSlot::create([
            'law_office_id' => $officeId,
            'date' => $request->date,
            'time_range' => $request->time_range,
            'capacity_remaining' => $request->capacity_remaining,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Slot created successfully',
            'slot' => $slot,
        ]);
    }

    /**
     * Update an appointment slot
     */
    public function updateSlot(Request $request, $officeId, $slotId)
    {
        $slot = AppointmentSlot::forOffice($officeId)->findOrFail($slotId);

        $request->validate([
            'time_range' => 'required|string',
            'capacity_remaining' => 'required|integer|min:0',
        ]);

        $slot->update($request->only(['time_range', 'capacity_remaining']));

        return response()->json([
            'status' => 'success',
            'message' => 'Slot updated successfully',
            'slot' => $slot,
        ]);
    }

    /**
     * Delete an appointment slot
     */
    public function destroySlot($officeId, $slotId)
    {
        $slot = AppointmentSlot::forOffice($officeId)->findOrFail($slotId);
        $slot->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Slot deleted successfully',
        ]);
    }

    /**
     * Store or update office date availability (calendar color/status)
     */
    public function storeAvailability(Request $request, $officeId)
    {
        $request->validate([
            'date' => 'required|date',
            'color' => 'required|string|in:green,yellow,red',
            'description' => 'nullable|string|max:500',
        ]);

        $availability = OfficeDateAvailability::updateOrCreate(
            ['law_office_id' => $officeId, 'date' => $request->date],
            [
                'color' => $request->color,
                'description' => $request->description ?? ''
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Availability updated successfully',
            'availability' => $availability,
        ]);
    }
}
