<?php

namespace App\Helpers;

use App\Models\AdminMessageNotif;
use App\Models\User;

class NotificationHelper
{
    public static function createMessageNotification($type, $title, $message, $senderInfo, $receiverId = null)
    {
        try {
            // If receiverId is not provided, send to all admins
            if (!$receiverId) {
                $admins = User::whereIn('role', ['admin', 'superadmin'])->get();
                
                foreach ($admins as $admin) {
                    AdminMessageNotif::create([
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'sender_id' => $senderInfo['id'] ?? null,
                        'sender_name' => $senderInfo['name'] ?? 'Unknown',
                        'sender_email' => $senderInfo['email'] ?? null,
                        'receiver_id' => $admin->id,
                        'is_read' => false
                    ]);
                }
                
                return true;
            }
            
            // Send to specific receiver
            AdminMessageNotif::create([
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'sender_id' => $senderInfo['id'] ?? null,
                'sender_name' => $senderInfo['name'] ?? 'Unknown',
                'sender_email' => $senderInfo['email'] ?? null,
                'receiver_id' => $receiverId,
                'is_read' => false
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to create message notification: ' . $e->getMessage());
            return false;
        }
    }
}