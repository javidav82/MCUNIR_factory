<?php

namespace Tests\Unit\API;

use Tests\TestCase;
use App\Http\Controllers\API\PrintStatusController;
use App\Models\PrintJob;
use App\Models\PrintStatus;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Mockery;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PrintStatusControllerTest extends TestCase
{
    use WithoutMiddleware;

    protected $controller;
    protected $printJobMock;
    protected $statusMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->printJobMock = Mockery::mock(PrintJob::class);
        $this->statusMock = Mockery::mock(PrintStatus::class);
        $this->controller = new PrintStatusController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test para verificar que el controlador se puede instanciar
     */
    public function test_controller_can_be_instantiated()
    {
        $this->assertInstanceOf(PrintStatusController::class, $this->controller);
    }
} 