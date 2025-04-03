<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CommitmentNote;
use App\Models\Notification;
use Carbon\Carbon;

class CheckPurchaseTerm extends Command
{
    protected $signature = 'check:purchase-term';
    protected $description = 'Verifica se o campo purchase_term está a 7 dias ou menos do vencimento e cria uma notificação';

    public function handle()
    {
        $today = Carbon::now();

        $notes = CommitmentNote::whereDate('purchase_term', '<=', $today->addDays(7))->get();

        foreach ($notes as $note) {
            $daysRemaining = Carbon::now()->diffInDays(Carbon::parse($note->purchase_term));

            $message = "A nota de compromisso #{$note->id} está a {$daysRemaining} dias do vencimento.";

            Notification::create([
                'description' => 'Vencimento Próximo',
                'message' => $message,
                'user_id' => $note->contract->user_id ?? null,
                'contract_id' => $notes->contract_id
            ]);
        }

        $this->info(count($notes) . ' notificações criadas.');
    }
}
