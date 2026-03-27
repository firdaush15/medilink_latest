<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Carbon\Carbon;

class CancelExpiredAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-expired-appointments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel all pending appointments whose time has already passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Appointment::where('status', 'Pending')
            ->where('appointment_date', '<', Carbon::now())
            ->update([
                'status' => 'Cancelled',
                'cancelled_reason' => 'Not approved in time'
            ]);

        $this->info("Cancelled {$count} expired pending appointments.");
    }
}
