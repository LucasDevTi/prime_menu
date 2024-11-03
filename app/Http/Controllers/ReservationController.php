<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function setReserva(Request $request)
    {
        $client = new Client();
        $address = new Address();
        $reservation = new Reservation();

        $name = $request->input('name');
        $cpf_cnpj = (!empty($request->input('cpf_cnpj'))) ? $request->input('cpf_cnpj') : '';
        $rua = (!empty($request->input('rua'))) ? $request->input('rua') : '';
        $bairro = (!empty($request->input('bairro'))) ? $request->input('bairro') : '';
        $numero = (!empty($request->input('numero'))) ? $request->input('numero') : '';
        $complemento = (!empty($request->input('complemento'))) ? $request->input('complemento') : '';
        $client_cellphone = (!empty($request->input('client_cellphone'))) ? $request->input('client_cellphone') : '';
        $client_email = (!empty($request->input('client_email'))) ? $request->input('client_email') : '';
        $client_obs = (!empty($request->input('client_obs'))) ? $request->input('client_obs') : '';
        $mesas_disponiveis = (!empty($request->input('mesas_disponiveis'))) ? $request->input('mesas_disponiveis') : '';

        $client->name = $name;
        $client->email = $client_email;
        $client->cpf_cnpj = $cpf_cnpj;
        $client->cellphone = $client_cellphone;
        $client->obs = $client_obs;

        if ($client->save()) {

            if (!empty($rua) && !empty($bairro)) {
                $address->client_id = $client->id;
                $address->street = $rua;
                $address->neighborhood = $bairro;
                $address->number = $numero;
                $address->complement = $complemento;
                $address->is_primary  = false;

                if ($address->save()) {
                    echo "salvou bairro<br>";
                }
            }

            echo "salvou clinte <br>";
            var_dump($client->id);

            $reservation->client_id = $client->id;
            $reservation->table_id = $mesas_disponiveis;
            $reservation->status = 1;

            if ($reservation->save()) {
                echo "reserva feita <br>";

                $table = Table::find($mesas_disponiveis);
                $table->status = 3;
                $table->description_status = "Reservada";
                $table->save();
                return redirect()->route('gestao')->with('success', 'Reserva criada com sucesso!');
            }
        } else {
            echo "não salvou";
        }

        var_dump($name);
        exit;
    }
}
