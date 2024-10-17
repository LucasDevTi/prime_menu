<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Addresses extends Model
{
    use HasFactory;

    protected $fillable = [
        'street',
        'neighborhood',
        'number',
        'complement'
    ];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_address', 'address_id', 'client_id')
                    ->withPivot('main');
    }


    //     // Associar endereços a um usuário
    // $user = User::find(1);
    // $user->addresses()->attach([1, 2]); // Adiciona os endereços com ID 1 e 2

    // // Obter todos os endereços de um usuário
    // $addresses = $user->addresses;

    // // Obter todos os usuários de um endereço
    // $address = Address::find(1);
    // $users = $address->users;
}
