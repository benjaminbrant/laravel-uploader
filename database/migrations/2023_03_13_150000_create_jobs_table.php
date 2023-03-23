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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->boolean('no_invoices_to_process')->nullable();
            $table->boolean('is_payload_error')->nullable();
            $table->string('payload_error_msg')->nullable();
            $table->boolean('is_upload_error')->nullable();
            $table->string('upload_error_msg')->nullable();
            $table->boolean('is_archive_error')->nullable();
            $table->string('archive_error_msg')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
