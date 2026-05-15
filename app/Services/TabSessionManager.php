<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TabSessionManager
{
    /**
     * Token expiration time in minutes (e.g., 24 hours)
     */
    public const TOKEN_EXPIRATION_MINUTES = 1440;

    /**
     * Generate a new per-tab session token for a user
     * 
     * @param User $user
     * @param string $tabId Client-generated unique tab identifier (UUID)
     * @return array ['tab_token' => token, 'tab_id' => tabId, 'expires_at' => ...] 
     */
    public static function generateTabToken(User $user, string $tabId): array
    {
        // Generate a secure random token
        $tabToken = Str::random(64);
        $expiresAt = Carbon::now()->addMinutes(self::TOKEN_EXPIRATION_MINUTES);

        // Clean up any old expired tokens for better hygiene
        self::cleanupExpiredTokens();

        // Store the token in the database
        DB::table('tab_sessions')->insert([
            'user_id' => $user->id,
            'tab_token' => $tabToken,
            'tab_id' => $tabId,
            'created_at' => Carbon::now(),
            'expires_at' => $expiresAt,
        ]);

        return [
            'tab_token' => $tabToken,
            'tab_id' => $tabId,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /**
     * Validate a per-tab token and return the user if valid
     * 
     * @param string $tabToken
     * @return User|null
     */
    public static function validateTabToken(string $tabToken): ?User
    {
        $session = DB::table('tab_sessions')
            ->where('tab_token', $tabToken)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$session) {
            return null;
        }

        return User::find($session->user_id);
    }

    /**
     * Get user and tab info for a given token
     * 
     * @param string $tabToken
     * @return array|null
     */
    public static function getTabSession(string $tabToken): ?array
    {
        $session = DB::table('tab_sessions')
            ->where('tab_token', $tabToken)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        return $session ? (array) $session : null;
    }

    /**
     * Logout a specific tab by token
     * Only logs out that specific tab's session, not the entire user session
     * 
     * @param string $tabToken
     * @return bool
     */
    public static function logoutTab(string $tabToken): bool
    {
        return DB::table('tab_sessions')
            ->where('tab_token', $tabToken)
            ->delete() > 0;
    }

    /**
     * Logout all tabs for a user
     * 
     * @param int $userId
     * @return int Number of tokens deleted
     */
    public static function logoutAllUserTabs(int $userId): int
    {
        return DB::table('tab_sessions')
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Cleanup expired tokens
     * Should be called periodically (via scheduler or on-demand)
     * 
     * @return int
     */
    public static function cleanupExpiredTokens(): int
    {
        return DB::table('tab_sessions')
            ->where('expires_at', '<', Carbon::now())
            ->delete();
    }

    /**
     * Extend the expiration of a tab token
     * Called during authenticated requests to keep the session alive
     * 
     * @param string $tabToken
     * @return bool
     */
    public static function refreshTabToken(string $tabToken): bool
    {
        $newExpiresAt = Carbon::now()->addMinutes(self::TOKEN_EXPIRATION_MINUTES);

        return DB::table('tab_sessions')
            ->where('tab_token', $tabToken)
            ->where('expires_at', '>', Carbon::now())
            ->update(['expires_at' => $newExpiresAt]) > 0;
    }

    /**
     * Get all active tabs for a user
     * 
     * @param int $userId
     * @return array
     */
    public static function getUserActiveTabs(int $userId): array
    {
        $sessions = DB::table('tab_sessions')
            ->where('user_id', $userId)
            ->where('expires_at', '>', Carbon::now())
            ->get();

        return $sessions->toArray();
    }
}
