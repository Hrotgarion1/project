<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            //España
            [
                'name' => 'Educación Infantil',
                'value' => '500',
                'pais_id' => '40'
            ],
            [
                'name' => 'Educación Primaria',
                'value' => '1000',
                'pais_id' => '40'
            ],
            [
                'name' => 'Educación Secundaria',
                'value' => '1500',
                'pais_id' => '40'
            ],
            [
                'name' => 'Bachillerato',
                'value' => '1500',
                'pais_id' => '40'
            ],
            [
                'name' => 'FP Grado Medio',
                'value' => '2500',
                'pais_id' => '40'
            ],
            [
                'name' => 'FP Grado superior',
                'value' => '2500',
                'pais_id' => '40'
            ],
            [
                'name' => 'Enseñanzas Artísticas y Deportivas',
                'value' => '1850',
                'pais_id' => '40'
            ],
            [
                'name' => 'Enseñanzas de Idiomas',
                'value' => '2125',
                'pais_id' => '40'
            ],
            [
                'name' => 'Enseñanzas Militares',
                'value' => '2970',
                'pais_id' => '40'
            ],
            [
                'name' => 'Estudios Universitarios',
                'value' => '3680',
                'pais_id' => '40'
            ],
            [
                'name' => 'Máster',
                'value' => '2760',
                'pais_id' => '40'
            ],
            [
                'name' => 'Doctorado',
                'value' => '5860',
                'pais_id' => '40'
            ],
    
            //Inglaterra
    
            [
                'name' => 'Early Years Foundation Stage (EYFS)',
                'value' => '500',
                'pais_id' => '44'
            ],
            [
                'name' => 'Key Stage 1 (KS1)',
                'value' => '450',
                'pais_id' => '44'
            ],
            [
                'name' => 'Key Stage 2 (KS2)',
                'value' => '1500',
                'pais_id' => '44'
            ],
            [
                'name' => 'Key Stage 3 (KS3)',
                'value' => '1600',
                'pais_id' => '44'
            ],
            [
                'name' => 'Key Stage 4 (KS4)',
                'value' => '2500',
                'pais_id' => '44'
            ],
            [
                'name' => 'Post-16 Education',
                'value' => '500',
                'pais_id' => '44'
            ],
            [
                'name' => 'Higher Education',
                'value' => '4500',
                'pais_id' => '44'
            ],
    
                
           ];
    
           foreach ($categories as $category) {
            Categoria::create($category);
                }
        }
}
