<?php

use App\Denominacione;
use Illuminate\Database\Seeder;

class DenominacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $denominaciones = [
            "cienmil" => 100000,
            "cincuentamil" => 50000,
            "veintemil" => 20000,
            "diezmil" => 10000,
            "cincomil" => 5000,
            "dosmil" => 2000,
            "mil" => 1000,
            "quinientos" => 500,
            "doscientos" => 200,
            "cien" => 100,
            "cincuenta" => 50,
        ];

        foreach ($denominaciones as $key => $value) {
            $denominacion = new Denominacione(["nombre" => $key, "valor" => $value, "existencia" => 0]);
            $denominacion->save();
        }
    }
}
