<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('document_name');
            $table->unsignedBigInteger('printer_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status')->default('pending');
            $table->integer('copies')->default(1);
            $table->boolean('color')->default(false);
            $table->boolean('double_sided')->default(false);
            $table->string('file_path');
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('printer_id')->references('id')->on('printers')->onDelete('cascade');

            // Indexes
            $table->index('status');
            $table->index('user_id');
            $table->index('printer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('print_jobs');
    }
};
