<?php

namespace Database\Seeders;

use App\Models\NotesStatus;
use Illuminate\Database\Seeder;

class NotesStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notesStatus = [
            [
                'name' => 'Em aberto',
                'color' => '#FFB400',
            ],
            [
                'name' => 'Cancelado',
                'color' => '#11d376',
            ],
            [
                'name' => 'Concluido',
                'color' => '#28A745',
            ],
        ];

        foreach ($notesStatus as $status) {
            NotesStatus::updateOrcreate($status, $status);
        }
    }
}
