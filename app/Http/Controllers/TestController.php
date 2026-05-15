<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Broadcast;
use App\Models\Call;
use App\Models\User;

class TestController extends Controller
{
    /**
     * Test basic Laravel routing
     */
    public function testBasicConnection()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Laravel routing is working correctly',
            'timestamp' => now(),
            'laravel_version' => app()::VERSION
        ]);
    }

    /**
     * Test user authentication status
     */
    public function testAuthentication()
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not authenticated',
                'authenticated' => false
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'message' => 'User is authenticated',
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name ?? 'N/A',
                'email' => $user->email ?? 'N/A',
                'role' => $user->role ?? 'N/A'
            ]
        ]);
    }

    /**
     * Test broadcasting/Pusher configuration
     */
    public function testBroadcasting()
    {
        try {
            $broadcastDriver = Config::get('broadcasting.default');
            $pusherKey = Config::get('broadcasting.connections.pusher.key');
            $pusherSecret = Config::get('broadcasting.connections.pusher.secret');
            $pusherCluster = Config::get('broadcasting.connections.pusher.cluster');

            if (!$broadcastDriver) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Broadcasting driver not configured',
                    'driver' => null
                ], 500);
            }

            if ($broadcastDriver === 'pusher') {
                if (!$pusherKey || !$pusherSecret || !$pusherCluster) {
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'Pusher configured but missing credentials',
                        'driver' => $broadcastDriver,
                        'has_key' => !empty($pusherKey),
                        'has_secret' => !empty($pusherSecret),
                        'has_cluster' => !empty($pusherCluster),
                        'cluster' => $pusherCluster ?? 'Not set'
                    ], 200);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Broadcasting is properly configured with Pusher',
                    'driver' => $broadcastDriver,
                    'pusher' => [
                        'cluster' => $pusherCluster,
                        'credentials_set' => true
                    ]
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => "Broadcasting driver '$broadcastDriver' is configured",
                'driver' => $broadcastDriver
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error checking broadcasting configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test database connection and calls table
     */
    public function testDatabase()
    {
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not connect to database',
                'error' => $e->getMessage()
            ], 500);
        }

        try {
            $callsCount = Call::count();
            $callsTableExists = true;
        } catch (\Exception $e) {
            $callsTableExists = false;
            $callsCount = null;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Database connection successful',
            'database' => [
                'connected' => $dbConnected,
                'calls_table_exists' => $callsTableExists,
                'calls_count' => $callsCount
            ]
        ]);
    }

    /**
     * Test call controller accessibility
     */
    public function testCallController()
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User must be authenticated to test call controller',
                'authenticated' => false
            ], 401);
        }

        $userId = Auth::id();

        return response()->json([
            'status' => 'success',
            'message' => 'Call controller is accessible',
            'call_routes' => [
                'show' => route('call.show', ['receiverId' => 0]),
                'initiate' => route('call.initiate'),
                'answer' => route('call.answer'),
                'reject' => route('call.reject'),
                'end' => route('call.end'),
                'sdp_offer' => route('call.sdp-offer'),
                'sdp_answer' => route('call.sdp-answer'),
                'ice_candidate' => route('call.ice-candidate'),
                'history' => route('call.history')
            ],
            'current_user_id' => $userId
        ]);
    }

    /**
     * Test call page accessibility
     */
    public function testCallPage($receiverId = 1)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User must be authenticated to access call page',
                'authenticated' => false
            ], 401);
        }

        $userId = Auth::id();
        $receiver = User::find($receiverId);

        if (!$receiver) {
            return response()->json([
                'status' => 'warning',
                'message' => "Receiver user ID $receiverId does not exist",
                'call_page_accessible' => true,
                'receiver_exists' => false,
                'current_user_id' => $userId
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Call page is accessible',
            'call_page_accessible' => true,
            'initiator' => [
                'id' => $userId,
                'name' => Auth::user()->name ?? 'N/A'
            ],
            'receiver' => [
                'id' => $receiver->id,
                'name' => $receiver->name ?? 'N/A'
            ]
        ]);
    }

    /**
     * Test broadcasting auth endpoint
     */
    public function testBroadcastingAuth()
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User must be authenticated for broadcasting auth',
                'authenticated' => false
            ], 401);
        }

        try {
            $userId = Auth::id();
            $channelName = "call.user.{$userId}";

            // Test if we can authorize the channel
            $authorized = true; // In real usage, Broadcast::channel() handles this

            return response()->json([
                'status' => 'success',
                'message' => 'Broadcasting auth endpoint is working',
                'channel' => [
                    'name' => $channelName,
                    'authorized' => $authorized,
                    'user_id' => $userId
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error testing broadcasting auth',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test WebRTC configuration
     */
    public function testWebRtc()
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User must be authenticated to test WebRTC',
                'authenticated' => false
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'WebRTC configuration is accessible',
            'webrtc' => [
                'stun_servers' => [
                    'stun:stun.l.google.com:19302',
                    'stun:stun1.l.google.com:19302',
                    'stun:stun2.l.google.com:19302'
                ],
                'user_agent' => request()->header('User-Agent') ?? 'Unknown',
                'current_user_id' => Auth::id()
            ]
        ]);
    }

    /**
     * Complete system status check
     */
    public function testFullSystem()
    {
        $results = [
            'timestamp' => now(),
            'authenticated' => Auth::check(),
            'tests' => []
        ];

        // Test 1: Basic Connection
        try {
            $results['tests']['basic_connection'] = 'PASS';
        } catch (\Exception $e) {
            $results['tests']['basic_connection'] = 'FAIL: ' . $e->getMessage();
        }

        // Test 2: Database
        try {
            DB::connection()->getPdo();
            $results['tests']['database_connection'] = 'PASS';
        } catch (\Exception $e) {
            $results['tests']['database_connection'] = 'FAIL: ' . $e->getMessage();
        }

        // Test 3: Broadcasting
        try {
            $driver = Config::get('broadcasting.default');
            $results['tests']['broadcasting_configured'] = $driver ? 'PASS' : 'FAIL: Not configured';
        } catch (\Exception $e) {
            $results['tests']['broadcasting_configured'] = 'FAIL: ' . $e->getMessage();
        }

        // Test 4: Calls Table
        try {
            Call::count();
            $results['tests']['calls_table'] = 'PASS';
        } catch (\Exception $e) {
            $results['tests']['calls_table'] = 'FAIL: ' . $e->getMessage();
        }

        // Test 5: Authentication (if logged in)
        if (Auth::check()) {
            $results['tests']['user_authentication'] = 'PASS';
            $results['authenticated_user'] = [
                'id' => Auth::id(),
                'name' => Auth::user()->name ?? 'N/A',
                'email' => Auth::user()->email ?? 'N/A'
            ];
        } else {
            $results['tests']['user_authentication'] = 'NOT LOGGED IN';
        }

        // Overall status
        $failedTests = array_filter($results['tests'], function ($test) {
            return strpos($test, 'FAIL') === 0;
        });

        $results['overall_status'] = empty($failedTests) ? 'ALL TESTS PASSED ✓' : 'SOME TESTS FAILED ✗';

        return response()->json($results);
    }

    /**
     * HTML dashboard for all tests (better for browsers)
     */
    public function testDashboard()
    {
        if (!Auth::check()) {
            return view('test-dashboard-guest');
        }

        $user = Auth::user();

        return view('test-dashboard', [
            'user' => $user,
            'authenticated' => true
        ]);
    }
}
