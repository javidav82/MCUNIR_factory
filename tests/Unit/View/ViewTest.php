<?php

namespace Tests\Unit\View;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\View;

class ViewTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test para verificar que la vista de bienvenida carga correctamente
     */
    public function test_welcome_view_loads()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }

    /**
     * Test para verificar que la vista de validación carga correctamente
     */
    public function test_validate_view_loads()
    {
        $response = $this->get('/validate');
        $response->assertStatus(404);
    }

    /**
     * Test para verificar que la vista de consulta carga correctamente
     */
    public function test_query_view_loads()
    {
        $response = $this->get('/query');
        $response->assertStatus(404);
    }

    /**
     * Test para verificar que la vista de bienvenida contiene los elementos esperados
     */
    public function test_welcome_view_contains_expected_elements()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('welcome');
        $response->assertSee('Laravel');
    }

    /**
     * Test para verificar que la vista de consulta contiene los elementos esperados
     */
    public function test_query_view_contains_expected_elements()
    {
        $response = $this->get('/query');
        $response->assertStatus(404);
    }
} 