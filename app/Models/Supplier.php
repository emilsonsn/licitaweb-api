<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    public $table = 'suppliers';

    public $fillable = [
        'user_id',
        'person_type',
        'name',
        'cpf_or_cnpj',
        'state_registration',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'landline_phone',
        'mobile_phone',
        'email',
    ];

    public function products()
    {
        return $this->hasMany(Products::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}