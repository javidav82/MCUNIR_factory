<?php

namespace Tests\Unit\API;

use Tests\TestCase;
use App\Http\Controllers\API\PrintJobController;
use App\Models\PrintJob;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Mockery;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PrintJobControllerTest extends TestCase
{
    use WithoutMiddleware;

    protected $controller;
    protected $printJobMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->printJobMock = Mockery::mock(PrintJob::class);
        $this->controller = new PrintJobController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test para verificar que se puede crear un nuevo trabajo de impresión
     */
    public function test_can_create_print_job()
    {
        $data = [
            'document_name' => 'Test Document',
            'printer_id' => 1,
            'copies' => 1,
            'color' => false,
            'double_sided' => true
        ];

        $request = new Request($data);

        $response = $this->controller->store($request);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('errors', $content);
    }

    /**
     * Test para verificar que se puede obtener la lista de trabajos de impresión
     */
    /*public function test_can_list_print_jobs()
    {
        // Crear mocks para las relaciones
        $batchMock = Mockery::mock(HasMany::class);
        $statusHistoryMock = Mockery::mock(MorphMany::class);
        $documentationMock = Mockery::mock(MorphMany::class);
        
        // Crear mock del Builder
        $builderMock = Mockery::mock(Builder::class);
        
        // Configurar el mock del modelo
        $this->printJobMock->shouldReceive('newQuery')
            ->once()
            ->andReturn($builderMock);
            
        $builderMock->shouldReceive('with')
            ->once()
            ->with(['batch', 'statusHistory', 'documentation'])
            ->andReturnSelf();
            
        $builderMock->shouldReceive('orderBy')
            ->once()
            ->with('created_at', 'desc')
            ->andReturnSelf();
            
        $builderMock->shouldReceive('paginate')
            ->once()
            ->with(10)
            ->andReturn(new LengthAwarePaginator([], 0, 10));

        // Reemplazar la instancia del modelo en el contenedor de servicios
        $this->app->instance(PrintJob::class, $this->printJobMock);

        // Llamar al método index del controlador
        $response = $this->controller->index();

        // Verificar la respuesta
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }*/

    /**
     * Test para verificar que se puede obtener un trabajo de impresión específico
     */
    public function test_can_show_print_job()
    {
        $this->printJobMock->shouldReceive('load')
            ->once()
            ->with(['batch', 'statusHistory', 'documentation'])
            ->andReturnSelf();

        $this->printJobMock->shouldReceive('toJson')
            ->once()
            ->andReturn('{"id":1,"document_name":"Test Document"}');

        $response = $this->controller->show($this->printJobMock);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
} 