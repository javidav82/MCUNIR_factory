<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintStatusTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('print_status_tracking', function (Blueprint $table) {
            $table->id();
            $table->morphs('trackable'); // This will create trackable_id and trackable_type columns and their index
            $table->enum('status', [
                'pending',
                'queued',
                'processing',
                'paused',
                'resumed',
                'completed',
                'failed',
                'cancelled'
            ]);
            $table->text('comments')->nullable();
            $table->string('changed_by')->nullable(); // User or system that changed the status
            $table->json('metadata')->nullable(); // Additional data about the status change
            $table->timestamps();

            // Add indexes for better performance
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('print_status_tracking');
    }
}
