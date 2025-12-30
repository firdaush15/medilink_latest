<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Support;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('account_completed')->default(false)->after('password');
            $table->boolean('registered_by_staff')->default(false)->after('account_completed');
            $table->string('account_completion_token')->nullable()->after('registered_by_staff');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_completed', 'registered_by_staff', 'account_completion_token']);
        });
    }
};