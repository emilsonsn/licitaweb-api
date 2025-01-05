<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenderStatus = [
            [                
                'name' => 'Pendente',
                'color' => '#FFB400'
            ],
            [
                'name' => 'Em andamento',
                'color' => '#007BFF'
            ],
            [
                'name' => 'Arrematado',
                'color' => '#11d376'
            ],
            [
                'name' => 'Finalizado',
                'color' => '#28A745'
            ],
        ];        

        foreach($tenderStatus as $status){
            Status::updateOrcreate($status, $status);
        }
    }
}
