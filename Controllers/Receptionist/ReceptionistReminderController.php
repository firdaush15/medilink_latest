<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use Illuminate\Http\Request;

class ReceptionistReminderController extends Controller
{
    /**
     * Show reminders dashboard
     */
    public function index()
    {
        $reminders = AppointmentReminder::with('appointment.patient.user', 'appointment.doctor.user')
            ->orderBy('scheduled_for', 'desc')
            ->paginate(50);

        $stats = [
            'pending' => AppointmentReminder::where('status', 'pending')->count(),
            'sent' => AppointmentReminder::where('status', 'sent')
                ->whereDate('sent_at', today())
                ->count(),
            'failed' => AppointmentReminder::where('status', 'failed')
                ->whereDate('created_at', today())
                ->count(),
        ];

        return view('receptionist.receptionist_reminders', compact('reminders', 'stats'));
    }

    /**
     * Create manual reminder for appointment
     */
    public function create(Request $request, $appointmentId)
    {
        $validated = $request->validate([
            'reminder_type' => 'required|in:sms,email',
        ]);

        $appointment = Appointment::with(['patient.user', 'doctor.user'])
            ->findOrFail($appointmentId);

        try {
            $reminder = AppointmentReminder::createForAppointment(
                $appointment,
                $validated['reminder_type']
            );

            return back()->with('success', 'Reminder scheduled successfully.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Failed to create reminder: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send reminder immediately
     */
    public function send($reminderId)
    {
        $reminder = AppointmentReminder::findOrFail($reminderId);

        try {
            // Mock sending (in production, integrate with SMS/Email service)
            $this->mockSendReminder($reminder);

            $reminder->markAsSent();

            return back()->with('success', 'Reminder sent successfully!');

        } catch (\Exception $e) {
            $reminder->markAsFailed($e->getMessage());
            return back()->withErrors([
                'error' => 'Failed to send reminder: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel pending reminder
     */
    public function cancel($reminderId)
    {
        $reminder = AppointmentReminder::findOrFail($reminderId);

        if ($reminder->status !== 'pending') {
            return back()->withErrors([
                'error' => 'Only pending reminders can be cancelled.'
            ]);
        }

        $reminder->update(['status' => 'cancelled']);

        return back()->with('success', 'Reminder cancelled successfully.');
    }

    /**
     * Send all pending reminders
     */
    public function sendAllPending()
    {
        $pendingReminders = AppointmentReminder::pending()->get();

        $sent = 0;
        $failed = 0;

        foreach ($pendingReminders as $reminder) {
            try {
                $this->mockSendReminder($reminder);
                $reminder->markAsSent();
                $sent++;
            } catch (\Exception $e) {
                $reminder->markAsFailed($e->getMessage());
                $failed++;
            }
        }

        return back()->with('success', 
            "Sent {$sent} reminder(s). " . 
            ($failed > 0 ? "{$failed} failed." : '')
        );
    }

    /**
     * Retry all failed reminders
     */
    public function retryFailed()
    {
        $failedReminders = AppointmentReminder::where('status', 'failed')->get();

        $sent = 0;
        $failed = 0;

        foreach ($failedReminders as $reminder) {
            try {
                $this->mockSendReminder($reminder);
                $reminder->markAsSent();
                $sent++;
            } catch (\Exception $e) {
                $reminder->markAsFailed($e->getMessage());
                $failed++;
            }
        }

        return back()->with('success', 
            "Retried {$sent} reminder(s). " . 
            ($failed > 0 ? "{$failed} still failed." : '')
        );
    }

    /**
     * Mock send reminder (replace with actual service in production)
     */
    private function mockSendReminder($reminder)
    {
        // Simulate sending
        // In production, integrate with:
        // - Twilio for SMS
        // - SendGrid/Mailgun for Email
        // - WhatsApp Business API
        
        \Log::info('Reminder sent', [
            'type' => $reminder->reminder_type,
            'recipient' => $reminder->recipient,
            'message' => $reminder->message_content,
        ]);

        // Simulate 95% success rate
        if (rand(1, 100) > 95) {
            throw new \Exception('Network error (simulated)');
        }
    }
}