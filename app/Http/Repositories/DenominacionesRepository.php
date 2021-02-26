<?php


namespace App\Http\Repositories;

use App\Denominacione;
use Exception;
use Illuminate\Support\Facades\DB;
use Mockery\Undefined;

class DenominacionesRepository
{

    static function calcularCambioDenominacion($denominacion, $monto, $disponible)
    {
        $salida = ["residuo" => ( $monto % $denominacion ), "cantidad" => 0];
        if ($salida["residuo"] == 0) {
            $salida["cantidad"] = $monto / $denominacion;
        } else {
            $salida["cantidad"] = ( $monto - $salida["residuo"] ) / $denominacion;
        }
        if ($salida["cantidad"] > $disponible) {
            $salida["residuo"] += ( $salida["cantidad"] - $disponible ) * $denominacion;
            $salida["cantidad"] = $disponible;
        }
        return $salida;
    }


    static function calcularCambio($denominaciones, $monto, $disponibles)
    {
        $minimoDisponible = array_filter($disponibles, function ($x) {
            return $x > 0;
        });
        $minimoDisponible = $denominaciones[array_keys($minimoDisponible)[count($minimoDisponible) - 1]];
        // dd($minimoDisponible);
        $out = [];
        $i = 0;
        foreach ($denominaciones as $key => $denominacion) {

            $calculoDenominacion = DenominacionesRepository::calcularCambioDenominacion($denominacion, $monto, $disponibles[$key] ?: 0);
            if ($calculoDenominacion["residuo"] == 0 || $calculoDenominacion["residuo"] >= $minimoDisponible) {
                $out[$key] = $calculoDenominacion["cantidad"];
                $monto = $calculoDenominacion["residuo"];
            } else {
                $out[$key] = 0;
            }
            if ($i == count($denominaciones) - 1 && $monto != 0) {
                throw new Exception('Imposible Dar Vuelto por que no hay sufiuciente dinero en caja o no hay sencillo');
            }
            $i++;
        }
        return $out;
    }

    static function validarDenominaciones($denominacionesIn)
    {
        $denominaciones = DenominacionesRepository::getDenominacionesDisponibles()['denominaciones'];
        if (count($denominacionesIn) == 0) {
            throw new Exception("La Entrada es requerida para poder cargar la caja");
        }
        foreach ($denominacionesIn as $key => $value) {
            if (!isset($denominaciones[$key])) {
                throw new Exception("La Entrada no es valida, la denominacion $key no existe");
            }

        }
    }

    static function getDenominacionesDisponibles()
    {
        $denominacionesBD = Denominacione::all();
        $denominaciones = [];
        $disponibles = [];
        foreach ($denominacionesBD as $denominacion) {
            $denominaciones[$denominacion["nombre"]] = $denominacion["valor"];
            $disponibles[$denominacion["nombre"]] = $denominacion["existencia"];
        }
        return ['denominaciones' => $denominaciones, 'disponibles' => $disponibles];
    }

    static function getMonto($denominacionesIn)
    {
        $monto = 0;
        $denominaciones = DenominacionesRepository::getDenominacionesDisponibles()['denominaciones'];
        foreach ($denominacionesIn as $key => $value) {
            $monto += $value * $denominaciones[$key];
        }
        return $monto;
    }

    static function cargarCaja($denominacionesIn)
    {
        foreach ($denominacionesIn as $key => $value) {
            $denominacion = Denominacione::where('nombre', $key)->first();
            $denominacion->update(["existencia" => ( $value ?: 0 ) + $denominacion->existencia]);
        }
    }

    static function vaciarCaja()
    {
        DB::update('update denominaciones set existencia = 0 where 1');
    }

    static function suma($arr1, $arr2)
    {
        foreach ($arr1 as $key => $value) {
            $arr2[$key] += $value ?: 0;
        }
        return $arr2;
    }

    static function descargarCaja($denominacionesIn, $disponibles)
    {
        foreach ($denominacionesIn as $key => $value) {
            Denominacione::where('nombre', $key)->update(["existencia" => $disponibles[$key] - $value ?: 0]);
        }
    }
}
