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
        Schema::create('mailbox_messages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('mailpit_id')->unique();
            $table->string('subject')->nullable();
            $table->string('preview', 512)->nullable();
            $table->enum('status', ['new', 'processed', 'expired'])->default('new')->index();
            $table->string('environment', 32)->default('local')->index();
            $table->timestamp('received_at')->index();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('size')->default(0);
            $table->longText('html_body')->nullable();
            $table->longText('text_body')->nullable();
            $table->json('headers')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['subject', 'received_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailbox_messages');
    }
};
