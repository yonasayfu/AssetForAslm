<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mailbox_recipients', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('message_id')->constrained('mailbox_messages')->cascadeOnDelete();
            $table->enum('type', ['from', 'to', 'cc', 'bcc'])->default('to');
            $table->string('email')->index();
            $table->string('name')->nullable();
            $table->timestamps();

            $table->unique(['message_id', 'email', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailbox_recipients');
    }
};
