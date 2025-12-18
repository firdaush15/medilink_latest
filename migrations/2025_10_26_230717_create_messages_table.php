<?php

// Migration 2: messages table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id('message_id');
            $table->foreignId('conversation_id')->constrained('conversations', 'conversation_id')->onDelete('cascade');
            
            // Sender (polymorphic)
            $table->foreignId('sender_id')->constrained('users', 'id')->onDelete('cascade');
            $table->enum('sender_type', ['doctor', 'patient', 'admin']); // Who sent this message
            
            // Message content
            $table->text('message_content');
            $table->string('attachment_path')->nullable(); // For file attachments
            
            // Message status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->enum('priority', ['normal', 'urgent'])->default('normal');
            
            // Important flags
            $table->boolean('is_system_message')->default(false); // Automated messages
            $table->boolean('requires_response')->default(false);
            
            $table->timestamps();
            
            // Index for performance
            $table->index(['conversation_id', 'created_at']);
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};