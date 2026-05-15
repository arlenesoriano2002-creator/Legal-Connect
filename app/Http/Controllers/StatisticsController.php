<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function index()
    {
        $totalAppointments = Appointment::count();

        $approvedCount = Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['approved'])->count();
        $pendingCount = Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['pending'])->count();
        $rejectedCount = Appointment::whereRaw("LOWER(TRIM(appointment_approval)) = ?", ['denied'])->count();

        $walkinsCount = DB::table('diffun_walkins')->count() + DB::table('cordon_walkins')->count();

        $approvalRate = $totalAppointments > 0
            ? round(($approvedCount / $totalAppointments) * 100)
            : 0;

        $activeLawyers = User::where('role', 'lawyer')->where('active_status', 1)->count();

        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        $labels = [];
        $appointmentTrend = [];
        $walkinTrend = [];

        $appointmentRecords = Appointment::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn ($item) => sprintf('%04d-%02d', $item->year, $item->month));

        $walkinRecords = collect(DB::table('diffun_walkins')
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('year', 'month')
            ->unionAll(
                DB::table('cordon_walkins')
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, count(*) as count')
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('year', 'month')
            )
            ->get())
            ->groupBy(fn ($item) => sprintf('%04d-%02d', $item->year, $item->month))
            ->map(fn ($group) => $group->sum('count'));

        for ($month = 0; $month < 6; $month++) {
            $date = $startDate->copy()->addMonths($month);
            $key = $date->format('Y-m');
            $labels[] = $date->format('M');
            $appointmentTrend[] = $appointmentRecords->has($key) ? $appointmentRecords->get($key)->count : 0;
            $walkinTrend[] = $walkinRecords->get($key, 0);
        }

        $topLawOffices = Appointment::selectRaw('law_office_id, count(*) as requests, sum(case when lower(trim(appointment_approval)) = "approved" then 1 else 0 end) as approved_count')
            ->whereNotNull('law_office_id')
            ->groupBy('law_office_id')
            ->orderByDesc('requests')
            ->limit(4)
            ->with('lawOffice')
            ->get()
            ->map(fn ($item) => [
                'law_office' => $item->lawOffice?->law_office ?? 'Unknown Office',
                'requests' => $item->requests,
                'approval' => $item->requests > 0 ? round(($item->approved_count / $item->requests) * 100) : 0,
                'status' => $item->requests > 0 && round(($item->approved_count / $item->requests) * 100) >= 80 ? 'Stable' : (round(($item->approved_count / $item->requests) * 100) >= 65 ? 'Rising' : 'Review'),
                'badge' => $item->requests > 0 && round(($item->approved_count / $item->requests) * 100) >= 80 ? 'badge-approved' : (round(($item->approved_count / $item->requests) * 100) >= 65 ? 'badge-pending' : 'badge-rejected'),
            ]);

        $todayStart = Carbon::today();
        $todayNewRequests = Appointment::where('created_at', '>=', $todayStart)->count();
        $todayApproved = Appointment::where('created_at', '>=', $todayStart)
            ->whereRaw('LOWER(TRIM(appointment_approval)) = ?', ['approved'])
            ->count();
        $todayRejected = Appointment::where('created_at', '>=', $todayStart)
            ->whereRaw('LOWER(TRIM(appointment_approval)) = ?', ['denied'])
            ->count();
        $todayWalkIns = DB::table('diffun_walkins')->where('created_at', '>=', $todayStart)->count()
            + DB::table('cordon_walkins')->where('created_at', '>=', $todayStart)->count();

        return view('statistics', compact(
            'totalAppointments',
            'walkinsCount',
            'approvalRate',
            'activeLawyers',
            'labels',
            'appointmentTrend',
            'walkinTrend',
            'approvedCount',
            'pendingCount',
            'rejectedCount',
            'topLawOffices',
            'todayNewRequests',
            'todayApproved',
            'todayWalkIns',
            'todayRejected'
        ));
    }
}
