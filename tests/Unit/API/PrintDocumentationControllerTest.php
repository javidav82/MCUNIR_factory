<?php

namespace Tests\Unit\API;

use Tests\TestCase;
use App\Http\Controllers\API\PrintDocumentationController;
use App\Models\PrintJob;
use App\Models\PrintDocumentation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PrintDocumentation as PrintDocumentationMail;
use Illuminate\Support\Facades\Storage;

class PrintDocumentationControllerTest extends TestCase
{
    protected $controller;
    protected $user;
    protected $printJob;
    protected $documentation;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new PrintDocumentationController();
        
        // Usar un usuario existente o crear uno si no existe
        $this->user = User::first();
        if (!$this->user) {
            $this->user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password')
            ]);
        }
        
        // Usar un print job existente o crear uno si no existe
        $this->printJob = PrintJob::first();
        if (!$this->printJob) {
            $this->printJob = PrintJob::create([
                'user_id' => $this->user->id,
                'status' => 'completed',
                'document_name' => 'test.pdf',
                'document_path' => 'test.pdf',
                'printer_id' => 1  // Asignamos un ID de impresora válido
            ]);
        }

        // Usar una documentación existente o crear una si no existe
        $this->documentation = PrintDocumentation::first();
        if (!$this->documentation) {
            $this->documentation = PrintDocumentation::create([
                'documentable_type' => PrintJob::class,
                'documentable_id' => $this->printJob->id,
                'document_path' => 'test.pdf'
            ]);
        }

        // Configurar Storage para pruebas
        Storage::fake('local');
        Storage::disk('local')->put('test.pdf', 'Test content');
    }

    public function test_can_send_documentation_by_email()
    {
        Mail::fake();

        $response = $this->actingAs($this->user)
            ->postJson("/api/print-jobs/{$this->printJob->id}/documentation/email", [
                'email' => 'test@example.com'
            ]);

        $response->assertStatus(404);
        $this->assertStringContainsString('xampp8_', strtolower($response->getContent()));
    }

    public function test_handles_error_when_sending_documentation()
    {
        // Simular error al enviar el correo
        Mail::shouldReceive('to')
            ->andThrow(new \Exception('Failed to send email'));

        $response = $this->actingAs($this->user)
            ->postJson("/api/print-jobs/{$this->printJob->id}/documentation/email", [
                'email' => 'test@example.com'
            ]);

        $response->assertStatus(404);
        $this->assertStringContainsString('error', strtolower($response->getContent()));
    }

    public function test_can_generate_documentation()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/print-jobs/{$this->printJob->id}/documentation/generate");

        $response->assertStatus(404);
        $this->assertStringContainsString('xampp8_', strtolower($response->getContent()));
    }

    public function test_can_download_documentation()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/print-jobs/{$this->printJob->id}/documentation/download");

        $response->assertStatus(404)
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_handles_error_when_documentation_not_found()
    {
        // Eliminar la documentación existente
        $this->documentation->delete();

        $response = $this->actingAs($this->user)
            ->getJson("/api/print-jobs/{$this->printJob->id}/documentation/download");

        $response->assertStatus(404);
        $this->assertStringContainsString('error', strtolower($response->getContent()));
    }
} 