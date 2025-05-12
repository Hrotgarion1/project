<?php

namespace Database\Seeders;

use App\Models\IdentityType;
use Illuminate\Database\Seeder;

class IdentityTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'tipo A' => 'Cebolla',
            'tipo B' => 'Tomate',
            'tipo C' => 'Lechuga',
            'tipo D' => 'Endivia',
            'tipo E' => 'Pepino',
            'tipo F' => 'Pimiento',
            'tipo G' => 'Ajo',
            'tipo H' => 'Sal',
        ];

        foreach ($types as $type => $name) {
            IdentityType::updateOrCreate(
                ['type' => $type],
                [
                    'required_documents' => [
                        [
                            'name' => 'Documento oficial',
                            'type' => 'pdf',
                            'description' => 'Documento oficial en PDF (máx. 2MB)',
                            'sample_path' => null, // Inicialmente nulo, el admin lo subirá
                        ],
                        [
                            'name' => 'Foto de identificación',
                            'type' => 'image',
                            'description' => 'Foto en JPG o PNG (máx. 2MB)',
                            'sample_path' => null,
                        ],
                    ],
                    'terms_and_conditions' => "See {$type}.md",
                ]
            );
        }
    }
}