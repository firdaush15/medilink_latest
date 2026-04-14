<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_tasks', function (Blueprint $table) {
            // Tracks when the overdue alert was last sent so we don't spam
            $table->timestamp('overdue_notified_at')
                ->nullable()
                ->after('cancelled_at')
                ->comment('When the overdue alert was sent; null = not yet notified');
        });
    }

    public function down(): void
    {
        Schema::table('staff_tasks', function (Blueprint $table) {
            $table->dropColumn('overdue_notified_at');
        });
    }
};