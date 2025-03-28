<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    public $table = 'clients';

    public $fillable = [
        'name',
        'type',
        'cpf_cnpj',
        'state_registration',
        'cep',
        'state',
        'city',
        'address',
        'number',
        'complement',
        'contact',
        'fix_phone',
        'whatsapp',
        'email',
        'user_id',
        'flag',
    ];

    public function responsible()
    {
        return $this->belongsTo(User::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function attachments(){
        return $this->hasMany(ClientAttachments::class);
    }

    public function logs()
    {
        return $this->hasMany(ClientLog::class);
    }
}
