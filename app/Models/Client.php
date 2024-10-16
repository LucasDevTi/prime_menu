<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'cpf_cnpj',
        'phone_1',
        'phone_2',
        'cellphone',
        'birth_date',
        'obs'
    ];

    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Addresses::class, 'client_address');
    }
}
