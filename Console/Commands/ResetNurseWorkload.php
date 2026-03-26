<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NurseAssignmentService;

class ResetNurseWorkload extends Command
{
    protected $signature = 'nurse:reset-workload';
    protected $description = 'Reset daily nurse workload counters';

    public function handle(NurseAssignmentService $assignmentService)
    {
        $this->info('Resetting nurse workload counters...');
        
        $assignmentService->resetDailyWorkload();
        
        $this->info('✅ Nurse workload counters reset successfully.');
        
        return Command::SUCCESS;
    }
}