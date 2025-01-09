<?php

namespace App\Services\Client;

use App\Models\Client;
use Exception;
use Illuminate\Support\Facades\Validator;

class ClientService
{
    public function all(){
        try {
            $clients = Client::orderBy('id', 'desc')
                ->get();

            return [
                'status' =>  true, 
                'data'   => $clients
            ];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term;
            $flag = $request->flag ?? null;

            $clients = Client::orderBy('id', 'desc');

            if(isset($search_term)){
                $clients->where('name', 'LIKE', "%{$search_term}%")
                    ->orWhere('cpf_cnpj', 'LIKE', "%{$search_term}%")
                    ->orWhere('email', 'LIKE', "%{$search_term}%")
                    ->orWhere('whatsapp', 'LIKE', "%{$search_term}%");
            }

            if(isset($flag)){
                $clients->where('flag', $flag);
            }

            $clients = $clients->paginate($perPage);

            return $clients;
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'in:Person,Company'],
                'cpf_cnpj' => ['required', 'string', 'max:255'],
                'state_registration' => ['nullable', 'string', 'max:255'],
                'cep' => ['nullable', 'string', 'max:255'],
                'state' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:255'],
                'number' => ['nullable', 'string', 'max:255'],
                'complement' => ['nullable', 'string', 'max:255'],
                'contact' => ['nullable', 'string', 'max:255'],
                'fix_phone' => ['nullable', 'string', 'max:255'],
                'whatsapp' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'string', 'max:255'],
                'user_id' => ['required', 'string', 'max:255'],
                'flag' => ['required', 'string', 'max:255'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];;
            }

            $client = Client::create($validator->validated());

            return ['status' => true, 'data' => $client];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }


    public function update($request, $user_id)
    {
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'in:Person,Company'], // Pessoa fisica, pessoa juridica
                'cpf_cnpj' => ['required', 'string', 'max:255'],
                'state_registration' => ['nullable', 'string', 'max:255'],
                'cep' => ['nullable', 'string', 'max:255'], // Colocou o cep ele precisa puxar as outras informações (API viacep)
                'state' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:255'],
                'number' => ['nullable', 'string', 'max:255'],
                'complement' => ['nullable', 'string', 'max:255'],
                'contact' => ['nullable', 'string', 'max:255'],
                'fix_phone' => ['nullable', 'string', 'max:255'],
                'whatsapp' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'string', 'max:255'],
                'user_id' => ['required', 'string', 'max:255'], // Responsável
                'flag' => ['required', 'string', 'max:255'], // Bandeira: Verde, amarela e vermelha
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) throw new Exception($validator->errors());

            $clientToUpdate = Client::find($user_id);

            if(!isset($clientToUpdate)) throw new Exception('Cliente não encontrado');

            $clientToUpdate->update($validator->validated());

            return ['status' => true, 'data' => $clientToUpdate];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function delete($id){
        try{
            $client = Client::find($id);

            if(!$client) throw new Exception('Cliente não encontrado');

            $clientName = $client->name;
            $client->delete();

            return ['status' => true, 'data' => $clientName];
        }catch(Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
