<?php

namespace App\Services\Client;

use App\Models\Client;
use App\Models\ClientAttachments;
use App\Models\ClientLog;
use App\Models\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientService
{
    public function all()
    {
        try {
            $clients = Client::with('user', 'attachments')->orderBy('id', 'desc')
                ->get();

            return [
                'status' => true,
                'data' => $clients,
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
            $user_id = $request->user_id ?? null;
            $location = $request->location ?? null;

            $clients = Client::with('user', 'attachments')->orderBy('id', 'desc');

            if (isset($search_term)) {
                $clients->where(function ($query) use ($search_term) {
                    $query->where('name', 'LIKE', "%{$search_term}%")
                        ->orWhere('cpf_cnpj', 'LIKE', "%{$search_term}%")
                        ->orWhere('email', 'LIKE', "%{$search_term}%")
                        ->orWhere('whatsapp', 'LIKE', "%{$search_term}%");
                });
            }

            if (isset($location)) {
                $clients->where(function ($query) use ($location) {
                    $query->where('cep', 'LIKE', "%{$location}%")
                        ->orWhere('state', 'LIKE', "%{$location}%")
                        ->orWhere('city', 'LIKE', "%{$location}%")
                        ->orWhere('address', 'LIKE', "%{$location}%");
                });
            }

            if (isset($flag)) {
                $clients->where('flag', $flag);
            }

            if (isset($user_id)) {
                $clients->where('user_id', $user_id);
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
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,png,xls,xlsx|max:2048',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            $client = Client::create($validator->validated());

            $attachments = [];
            if ($request->attachments) {
                foreach ($request->attachments as $attachment) {
                    $path = $attachment->store('clients/attachments', 'public');
                    $fullPath = 'storage/' . $path;

                    $attachments[] = ClientAttachments::create([
                        'client_id' => $client->id,
                        'filename' => $attachment->getClientOriginalName(),
                        'path' => $fullPath,
                    ]);
                }
            }

            Log::create([
                'description' => 'Criou um cliente',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $client];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $client_id)
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
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,png,xls,xlsx|max:2048',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            $clientToUpdate = Client::find($client_id);

            if (!isset($clientToUpdate)) {
                throw new Exception('Cliente não encontrado');
            }

            $clientToUpdate->update($validator->validated());

            $attachments = [];
            if ($request->attachments) {
                foreach ($request->attachments as $attachment) {
                    $path = $attachment->store('tenders/attachments', 'public');
                    $fullPath = 'storage/' . $path;

                    $attachments[] = ClientAttachments::updateOrCreate(
                        [
                            'client_id' => $clientToUpdate->id,
                            'filename' => $attachment->getClientOriginalName(),
                            'path' => $fullPath,
                        ]);
                }
            }

            ClientLog::create([
                'description' => 'Cliente foi atualizado',
                'user_id' => Auth::user()->id,
                'client_id' => $client_id,
                'request' => json_encode($request->all())
            ]);

            Log::create([
                'description' => 'Atualizou um cliente',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $clientToUpdate];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function delete($id): array
    {
        try {
            $client = Client::find($id);

            if (!$client) {
                throw new Exception('Cliente não encontrado');
            }

            $clientAttachments = ClientAttachments::where('client_id', $client->id);

            if ($clientAttachments) {
                $clientAttachments->delete();
            }

            $clientName = $client->name;
            $client->delete();

            Log::create([
                'description' => 'Deletou um cliente',
                'user_id' => Auth::user()->id,
                'request' => json_encode(['object' => $client->object]),
            ]);

            return ['status' => true, 'data' => $clientName];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function deleteAttachment($attachmentId)
    {
        try {
            $attachment = ClientAttachments::find($attachmentId);

            if (!$attachment) {
                throw new Exception('Anexo não encontrado');
            }

            $attachmentId = $attachment->id;
            $attachment->delete();

            $filename = $attachment->filename;

            ClientLog::create([
                'description' => 'Um arquivo vinculado ao cliente foi deletado',
                'user_id' => Auth::user()->id,
                'client_id' => $attachment->client_id,
                'request' => json_encode(['name' => $filename])
            ]);

            Log::create([
                'description' => 'Deletou um anexo de cliente',
                'user_id' => Auth::user()->id,
                'request' => json_encode(['name' => $filename]),
            ]);

            return ['status' => true, 'data' => ['attachmentId' => $attachmentId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
