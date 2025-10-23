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
        Schema::create('mailbox_attachments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('message_id')->constrained('mailbox_messages')->cascadeOnDelete();
            $table->string('mailpit_attachment_id')->nullable();
            $table->string('filename');
            $table->string('disk', 64);
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('checksum', 128)->nullable();
            $table->timestamps();

            $table->index(['message_id', 'filename']);
            $table->unique(['message_id', 'mailpit_attachment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailbox_attachments');
    }
};
