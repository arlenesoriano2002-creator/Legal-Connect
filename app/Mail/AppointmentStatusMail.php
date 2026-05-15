<?php

namespace App\Mail;

use App\Models\CaseCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AppointmentStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $appointment;
    public $status;
    public $appointmentData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($appointment, $status = 'approved')
    {
        $this->appointment = $appointment;
        $this->status = strtolower($status);
        
        // Create a backup of critical appointment data to ensure it's available
        // even if SerializesModels causes issues during queue processing
        // PRIORITY: selected_date/selected_time > schedule_date/schedule_time > fallback
        $selectedDate = $appointment->selected_date ?? $appointment->schedule_date ?? '';
        $selectedTime = $appointment->selected_time ?? $appointment->schedule_time ?? '';
        
        $this->appointmentData = [
            'fullname' => trim($appointment->fullname ?? 'Valued Client'),
            'email' => trim($appointment->email ?? ''),
            'phone' => trim($appointment->phone ?? ''),
            'selected_date' => trim($selectedDate),
            'selected_time' => trim($selectedTime),
            'category' => trim($appointment->category ?? 'N/A'),
            'case_name' => trim($appointment->case_name ?? 'N/A'),
            'service_fee_text' => $this->resolveServiceFeeText($appointment),
            'selected_branch' => trim($appointment->selected_branch ?? 'N/A'),
        ];
        
        // Log with detailed info for debugging date/time issues
        \Log::info('AppointmentStatusMail instantiated - CRITICAL DATA CAPTURED', [
            'appointment_id' => $appointment->id ?? 'unknown',
            'status' => $this->status,
            'email' => $this->appointmentData['email'],
            'selected_date' => $this->appointmentData['selected_date'],
            'selected_time' => $this->appointmentData['selected_time'],
            'schedule_date' => $appointment->schedule_date ?? 'N/A',
            'schedule_time' => $appointment->schedule_time ?? 'N/A',
            'raw_selected_date_type' => gettype($appointment->selected_date),
            'raw_selected_time_type' => gettype($appointment->selected_time),
        ]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = $this->status === 'denied' ? 'emails.appointment_denied' : 'emails.appointment_approved';
        $subject = $this->status === 'denied' ? 'Appointment Status Update - LegalConnect' : 'Appointment Approved - LegalConnect';

        $fromAddress = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS') ?: env('MAIL_USERNAME');
        $fromName = config('mail.from.name') ?: env('MAIL_FROM_NAME') ?: 'LegalConnect';

        // Log detailed info about what will be sent in the email
        \Log::info('AppointmentStatusMail building email - VERIFYING DATA PASSED TO TEMPLATE', [
            'view' => $view,
            'subject' => $subject,
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'appointmentData_date' => $this->appointmentData['selected_date'] ?? 'MISSING!',
            'appointmentData_time' => $this->appointmentData['selected_time'] ?? 'MISSING!',
            'appointmentData_fullname' => $this->appointmentData['fullname'] ?? 'MISSING!',
            'appointmentData_email' => $this->appointmentData['email'] ?? 'MISSING!',
            'appointmentData_service_fee_text' => $this->appointmentData['service_fee_text'] ?? 'MISSING!',
            'appointment_object_date' => $this->appointment->selected_date ?? 'null',
            'appointment_object_time' => $this->appointment->selected_time ?? 'null',
        ]);

        $mail = $this->view($view)
                     ->with([
                         'appointment' => $this->appointment,
                         'appointmentData' => $this->appointmentData
                     ])
                     ->subject($subject);

        if ($fromAddress) {
            $mail->from($fromAddress, $fromName);
        }

        return $mail;
    }

    private function resolveServiceFeeText($appointment): string
    {
        $caseName = trim((string) ($appointment->case_name ?? ''));
        $category = trim((string) ($appointment->category ?? ''));

        if ($caseName === '' || $category === '') {
            return 'Not set yet';
        }

        $serviceFee = CaseCategory::where('category', $category)
            ->where('case_name', $caseName)
            ->value('service_fee');

        if ($serviceFee === null || $serviceFee === '') {
            return 'Not set yet';
        }

        return "\u{20B1}" . number_format((float) $serviceFee, 2);
    }
}

