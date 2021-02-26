<?php

namespace App\Http\Controllers;

use App\Caja;
use App\Denominacione;
use Illuminate\Http\Request;
use App\Http\Repositories\DenominacionesRepository;
use App\Http\Requests\CargarCajaRequest;
use App\Http\Requests\estadoCajaRequest;
use App\Http\Requests\RealizarPagoRequest;
use App\Movimiento;
use Exception;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function cargarCaja()
    {
        try {
            DenominacionesRepository::validarDenominaciones(request()->all());
            DenominacionesRepository::cargarCaja(request()->all());
            $monto_movimiento = DenominacionesRepository::getMonto(request()->all());
            $disponibles = DenominacionesRepository::getDenominacionesDisponibles()['disponibles'];
            $monto_final_caja = DenominacionesRepository::getMonto($disponibles);

            Movimiento::create([
                'tipo_movimiento' => 'cargarCaja',
                'monto_movimiento' => $monto_movimiento,
                'monto_final_caja' => $monto_final_caja,
                'detalle_estado' => json_encode($disponibles)
            ]);

            $estadoCaja = Movimiento::select('monto_final_caja', 'monto_movimiento', 'detalle_estado')->orderBy('created_at', 'desc')->first();
            $estadoCaja->detalle_estado = json_decode($estadoCaja->detalle_estado);

            return response()->json(["success" => true, "message" => "exito", "data" => $estadoCaja]);
        } catch (\Throwable $th) {
            return response()->json(["success" => false, "message" => $th->getMessage(), "data" => []], 404);
        }
    }


    public function vaciarCaja()
    {
        try {
            $disponibles = DenominacionesRepository::getDenominacionesDisponibles()['disponibles'];
            $monto_movimiento = DenominacionesRepository::getMonto($disponibles);
            DenominacionesRepository::vaciarCaja();
            $disponibles = DenominacionesRepository::getDenominacionesDisponibles()['disponibles'];
            Movimiento::create([
                'tipo_movimiento' => 'vaciarCaja',
                'monto_movimiento' => $monto_movimiento,
                'monto_final_caja' => 0,
                'detalle_estado' => json_encode($disponibles)
            ]);

            $estadoCaja = Movimiento::select('monto_final_caja', 'monto_movimiento', 'detalle_estado')->orderBy('created_at', 'desc')->first();
            $estadoCaja->detalle_estado = json_decode($estadoCaja->detalle_estado);

            return response()->json(["success" => true, "message" => "exito", "data" => $estadoCaja]);
        } catch (\Throwable $th) {
            return response()->json(["success" => false, "message" => $th->getMessage(), "data" => []], 404);
        }
    }

    public function estadoCaja()
    {
        try {
            // $estadoCaja = DenominacionesRepository::getDenominacionesDisponibles()['disponibles'];
            $estadoCaja = Movimiento::select('monto_final_caja', 'detalle_estado')->orderBy('created_at', 'desc')->first();
            $estadoCaja->detalle_estado = json_decode($estadoCaja->detalle_estado);
            return response()->json(["success" => true, "message" => "exito", "data" => $estadoCaja ?: []]);
        } catch (\Throwable $th) {
            return response()->json(["success" => false, "message" => $th->getMessage(), "data" => []], 404);
        }
    }

    public function realizarPago(RealizarPagoRequest $request)
    {
        try {

            DenominacionesRepository::validarDenominaciones(request()->denominaciones);
            ['denominaciones' => $denominaciones, 'disponibles' => $disponibles] = DenominacionesRepository::getDenominacionesDisponibles();
            $pagacon = DenominacionesRepository::getMonto(request()->denominaciones);
            $disponibles = DenominacionesRepository::suma(request()->denominaciones, $disponibles);
            $vuelto = $pagacon - request()->cobro;
            if ($vuelto < 0) {
                throw new Exception('Imposible dar Vuelto por que no le alcanzal el dinero para poder pagar');
            }
            $cambio = DenominacionesRepository::calcularCambio($denominaciones, $vuelto, $disponibles);
            DenominacionesRepository::descargarCaja($cambio, $disponibles);
            $monto_final_caja = DenominacionesRepository::getMonto($disponibles);


            Movimiento::create([
                'tipo_movimiento' => 'realizarPago',
                'monto_movimiento' => request()->cobro,
                'monto_final_caja' => $monto_final_caja,
                'detalle_estado' => json_encode($disponibles)
            ]);

            $estadoCaja = Movimiento::select('monto_final_caja', 'monto_movimiento', 'detalle_estado')->orderBy('created_at', 'desc')->first();
            $estadoCaja->detalle_estado = json_decode($estadoCaja->detalle_estado);


            return response()->json(["success" => true, "message" => "exito", "data" => $estadoCaja]);
        } catch (\Throwable $th) {
            return response()->json(["success" => false, "message" => $th->getMessage(), "data" => []], 404);
        }
    }

    public function logEventos()
    {
        try {
            $movimientos = Movimiento::select('monto_movimiento', 'monto_final_caja', 'detalle_estado')->get();

            foreach ($movimientos as $key => $x) {
                $x->detalle_estado = json_decode($x->detalle_estado);
            }

            return response()->json(["success" => true, "message" => "exito", "data" => $movimientos ?: []]);
        } catch (\Throwable $th) {
            return response()->json(["success" => false, "message" => $th->getMessage(), "data" => []], 404);
        }
    }

    public function estadoCajaFecha(EstadoCajaRequest $request)
    {
        try {
            $movimiento = Movimiento::select('monto_movimiento', 'monto_final_caja', 'detalle_estado')->whereNotBetween('created_at', [request()->fecha, now()])->orderBy('created_at', 'desc')->first();
            $movimiento->detalle_estado = json_decode($movimiento->detalle_estado);

            return response()->json(["success" => true, "message" => "exito", "data" => $movimiento ?: []], 200);
        } catch (\Throwable $th) {
            return response()->json(["success" => false, "message" => $th->getMessage(), "data" => []], 404);
        }
    }
}
