<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Belonging;
use App\Models\Paises;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class BelongingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Obtener todos los países
        $paises = Paises::all();

        if ($paises->isEmpty()) {
            throw new \Exception('No hay países en la base de datos. Ejecute el PaisSeeder primero.');
        }

        foreach ($paises as $pais) {
            // Generar entre 1 y 5
            $numStates = $faker->numberBetween(1, 5);
            $stateGenerados = [];

            for ($i = 0; $i < $numStates; $i++) {
                // Generar uno único
                do {
                    $state = $faker->state();
                } while (in_array($state, $stateGenerados));

                // Añadirlo a la lista para evitar repeticiones
                $stateGenerados[] = $state;

                Belonging::create([
                    'name' => $state,
                    'country_id' => $pais->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
