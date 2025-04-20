<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('print_documentations', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');
            $table->string('document_path');
            $table->string('document_name')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('print_documentations');
    }
};
