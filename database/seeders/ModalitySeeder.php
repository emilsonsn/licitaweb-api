<?php

namespace Database\Seeders;

use App\Models\Modality;
use Illuminate\Database\Seeder;

class ModalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modalities = [
            ['name' => 'Concorrência', 'description' => 'Modalidade para contratações de maior valor e complexidade.'],
            ['name' => 'Tomada de Preços', 'description' => 'Modalidade para empresas cadastradas em valores intermediários.'],
            ['name' => 'Convite', 'description' => 'Modalidade para contratos de menor valor com convite direto a fornecedores.'],
            ['name' => 'Concurso', 'description' => 'Usada para premiar trabalhos técnicos, científicos ou artísticos.'],
            ['name' => 'Leilão Presencial', 'description' => 'Modalidade para venda de bens públicos ao maior lance.'],
            ['name' => 'Leilão Eletrônico', 'description' => 'Modalidade para venda de bens públicos ao maior lance.'],
            ['name' => 'Pregão Presencial', 'description' => 'Usada para aquisição de bens e serviços comuns com foco no menor preço.'],
            ['name' => 'Pregão Eletrônico', 'description' => 'Usada para aquisição de bens e serviços comuns com foco no menor preço.'],
        ];

        foreach ($modalities as $modality) {
            Modality::firstOrCreate(['name' => $modality['name']], $modality);
        }
    }
}
