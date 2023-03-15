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
            $table->unsignedBigInteger('job_id');
            $table->string('po');
            $table->string('filename');
            $table->integer('local_size')->nullable();
            $table->integer('remote_size')->nullable();
            $table->boolean('is_uploaded')->nullable();
            $table->boolean('is_processed')->nullable();
            $table->boolean('is_identical_filesize')->nullable();
            $table->string('archive_location')->nullable();
            $table->timestamps();
            $table->foreign('job_id')
                ->references('id')->on('jobs')->onDelete('cascade');
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
