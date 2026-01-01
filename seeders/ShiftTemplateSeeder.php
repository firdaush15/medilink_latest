<?php
// database/seeders/ShiftTemplateSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShiftTemplate;

class ShiftTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'template_name' => 'Morning Shift',
                'start_time' => '08:00:00',
                'end_time' => '14:00:00',
                'duration_hours' => 6,
                'color_code' => '#4CAF50',
            ],
            [
                'template_name' => 'Afternoon Shift',
                'start_time' => '14:00:00',
                'end_time' => '20:00:00',
                'duration_hours' => 6,
                'color_code' => '#FF9800',
            ],
            [
                'template_name' => 'Full Day',
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
                'duration_hours' => 12,
                'color_code' => '#2196F3',
            ],
            [
                'template_name' => 'Saturday Half-Day',
                'start_time' => '08:00:00',
                'end_time' => '14:00:00',
                'duration_hours' => 6,
                'color_code' => '#9C27B0',
            ],
        ];
        
        foreach ($templates as $template) {
            ShiftTemplate::create($template);
        }
        
        $this->command->info('✅ Shift templates seeded successfully!');
    }
}