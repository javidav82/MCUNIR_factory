<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintDocumentationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('print_documentation', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable'); // Links to either batch or individual job
            $table->enum('document_type', [
                'feedback',
                'issue',
                'expedition',
                'quality_check',
                'correction',
                'other'
            ]);
            $table->string('title');
            $table->text('description');
            $table->string('document_path')->nullable(); // Path to uploaded document
            $table->string('document_name')->nullable(); // Original name of the document
            $table->string('document_type_file')->nullable(); // Type of document (pdf, doc, etc.)
            $table->integer('document_size')->nullable(); // Size in bytes
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('metadata')->nullable(); // Additional data in JSON format
            $table->timestamps();

            // Add indexes for better performance
            $table->index('document_type');
            $table->index('priority');
            $table->index('status');
            $table->index('created_at');
        });

        // Create a table for document comments/updates
        Schema::create('print_documentation_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documentation_id')->constrained('print_documentation')->onDelete('cascade');
            $table->text('comment');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('attachments')->nullable(); // JSON array of attachment paths
            $table->timestamps();

            // Add index for better performance
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
        Schema::dropIfExists('print_documentation_updates');
        Schema::dropIfExists('print_documentation');
    }
}
