<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'staff_id',
        'sender_id',
        'appointment_id',
        'type',
        'title',
        'message',
        'assigned_to',
        'is_read',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the attributes that should be appended.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'time_ago',
        'icon',
    ];

    /**
     * Relationship: Notification belongs to a staff user.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Relationship: Notification belongs to a sender user.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relationship: Notification may belong to an appointment.
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Scope: Get notifications for a specific staff member.
     */
    public function scopeForStaff($query, $staffId)
    {
        return $query->where(function($q) use ($staffId) {
            $q->where('staff_id', $staffId)
              ->orWhere('assigned_to', 'all');
        });
    }

    /**
     * Scope: Get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Get recent notifications.
     */
    public function scopeRecent($query, $limit = 15)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
        return $this;
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread()
    {
        $this->update(['is_read' => false]);
        return $this;
    }

    /**
     * Check if notification is read.
     */
    public function isRead()
    {
        return $this->is_read === true;
    }

    /**
     * Check if notification is unread.
     */
    public function isUnread()
    {
        return $this->is_read === false;
    }

    /**
     * Get the time ago attribute.
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the icon based on notification type.
     */
    public function getIconAttribute()
    {
        $icons = [
            'pending_request' => 'calendar-plus',
            'appointment_update' => 'calendar-check',
            'message_inquiry' => 'envelope',
            'system' => 'bell',
            'message' => 'envelope',
            'task' => 'tasks',
            'alert' => 'exclamation-triangle',
            'info' => 'info-circle',
            'test' => 'bell',
        ];

        return $icons[$this->type] ?? 'bell';
    }

    /**
     * Get the CSS class based on notification type.
     */
    public function getTypeClassAttribute()
    {
        $classes = [
            'pending_request' => 'notification-type-pending',
            'appointment_update' => 'notification-type-update',
            'message_inquiry' => 'notification-type-message',
            'system' => 'notification-type-system',
            'message' => 'notification-type-message',
            'task' => 'notification-type-task',
            'alert' => 'notification-type-alert',
            'info' => 'notification-type-info',
            'test' => 'notification-type-test',
        ];

        return $classes[$this->type] ?? 'notification-type-default';
    }

    /**
     * Get the notification type label.
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'pending_request' => 'New Appointment',
            'appointment_update' => 'Appointment Update',
            'message_inquiry' => 'Message Inquiry',
            'system' => 'System',
            'message' => 'Message',
            'task' => 'Task',
            'alert' => 'Alert',
            'info' => 'Information',
            'test' => 'Test',
        ];

        return $labels[$this->type] ?? 'Notification';
    }

    /**
     * Static method to get unread count for a staff member.
     */
    public static function getUnreadCountForStaff($staffId)
    {
        return self::where(function($query) use ($staffId) {
                $query->where('staff_id', $staffId)
                      ->orWhere('assigned_to', 'all');
            })
            ->where('is_read', false)
            ->count();
    }

    /**
     * Static method to mark all as read for a staff member.
     */
    public static function markAllAsReadForStaff($staffId)
    {
        return self::where(function($query) use ($staffId) {
                $query->where('staff_id', $staffId)
                      ->orWhere('assigned_to', 'all');
            })
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Create a notification for staff.
     */
    public static function createNotification($data)
    {
        return self::create([
            'staff_id' => $data['staff_id'] ?? null,
            'sender_id' => $data['sender_id'] ?? null,
            'appointment_id' => $data['appointment_id'] ?? null,
            'type' => $data['type'] ?? 'system',
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['message'] ?? '',
            'assigned_to' => $data['assigned_to'] ?? 'individual',
            'is_read' => $data['is_read'] ?? false,
        ]);
    }

    /**
     * Format the notification for API response.
     */
    public function formatForApi()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'type_class' => $this->type_class,
            'icon' => $this->icon,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at->toDateTimeString(),
            'time_ago' => $this->time_ago,
            'appointment' => $this->appointment ? [
                'id' => $this->appointment->id,
                'fullname' => $this->appointment->fullname,
                'selected_date' => $this->appointment->selected_date,
                'selected_time' => $this->appointment->selected_time,
                'appointment_approval' => $this->appointment->appointment_approval,
            ] : null,
            'sender' => $this->sender ? [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'email' => $this->sender->email,
                'role' => $this->sender->role,
            ] : null,
        ];
    }

    /**
     * Get the URL for the notification action.
     */
    public function getActionUrlAttribute()
    {
        switch ($this->type) {
            case 'pending_request':
            case 'appointment_update':
                return route('appointments.show', $this->appointment_id);
            case 'message':
                return route('messages.show', $this->sender_id);
            default:
                return '#';
        }
    }
}
