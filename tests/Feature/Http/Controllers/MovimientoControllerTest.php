<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MovimientoControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    public function testCargarCaja()
    {
        $response = $this->post('/api/cargarcaja', ["diezmil" => 12]);
        $response->assertStatus(200);
    }

    public function testCargarCajaValidateError()
    {
        $response = $this->post('/api/cargarcaja', ["denominacionNoRegistrada" => 12]);
        $response->assertStatus(404);
    }

    public function testVaciarCaja()
    {
        $response = $this->get('/api/vaciarcaja');
        $response->assertStatus(200);
    }

    public function testEstadoCaja()
    {
        $response = $this->get('/api/estadocaja');
        $response->assertStatus(200);
    }

  
    public function testRealizarPago()
    {
        $response = $this->post(
            '/api/realizarpago',
            [
                "cobro" => 5000,
                "denominaciones" => ["cincomil" => 1]
            ]
        );
        $response->assertStatus(200);
    }

    public function testRealizarPagoValidateError()
    {
        $response = $this->post(
            '/api/realizarpago',
            [
                "cobro" => 5000,
                "denominaciones" => ["denominacionNoRegistrada" => 1]
            ]
        );
        $response->assertStatus(404);
    }

    public function testLogEventos()
    {
        $response = $this->get('/api/logeventos');
        $response->assertStatus(200);
    }

    public function testLogEventosFecha()
    {
        $response = $this->post('/api/estadocajafecha', ["fecha" => "2021-02-26 15:51:31"]);
        $response->assertStatus(200);
    }
    public function testLogEventosFechaValidateError()
    {
        $response = $this->post('/api/estadocajafecha', ["fecha" => "fecha Invalida"]);
        $response->assertStatus(302);
    }
    

}
