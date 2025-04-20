<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintJobsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Print Batches table
        Schema::create('print_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_name');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('total_files')->default(0);
            $table->integer('processed_files')->default(0);
            $table->integer('failed_files')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Print Jobs table
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('print_batches')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size');
            $table->integer('page_count')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Print Job Logs table
        Schema::create('print_job_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('print_jobs')->onDelete('cascade');
            $table->string('action');
            $table->text('message');
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('print_job_logs');
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('print_batches');
    }
}
