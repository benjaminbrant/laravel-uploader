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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('po');
            $table->string('filename');
            $table->integer('local_size');
            $table->integer('remote_size');
            $table->boolean('is_uploaded');
            $table->boolean('is_processed');
            $table->boolean('is_identical_filesize');
            $table->string('archive_location');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
