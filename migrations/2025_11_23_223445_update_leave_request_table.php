<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->decimal('days', 4, 1)->default(1.0)->after('end_date');
            $table->tinyInteger('is_half_day')->default(0)->after('days');
            $table->text('rejection_reason')->nullable()->after('admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['days', 'is_half_day', 'rejection_reason']);
        });
    }
};