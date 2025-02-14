<?php

namespace App\Services\Contract;

use App\Models\Contract;
use App\Models\ContractFile;
use App\Models\ContractPayment;
use App\Models\ContractProduct;
use App\Models\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContractService
{
    public function all()
    {
        try {
            $contracts = Contract::with('client', 'attachments', 'tender.attachments')->get();

            return ['status' => true, 'data' => $contracts];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;
            $client_id = $request->client_id ?? null;
            $tender_id = $request->tender_id ?? null;
            $status = $request->status ?? null;
            $start_date = $request->start_date ?? null;
            $end_date = $request->end_date ?? null;

            $contracts = Contract::with('client', 'attachments', 'tender.attachments');

            if (isset($search_term)) {
                $contracts->where('contract_number', 'LIKE', "%{$search_term}%")
                    ->orWhere('contract_object', 'LIKE', "%{$search_term}%");
            }

            if (isset($client_id)) {
                $contracts->where('client_id', $client_id);
            }

            if (isset($tender_id)) {
                $contracts->where('tender_id', $tender_id);
            }

            if (isset($status)) {
                $contracts->where('status', $status);
            }

            if (isset($start_date) && isset($end_date)) {
                if ($start_date == $end_date) {
                    $contracts->whereDate('signature_date', $start_date);
                } else {
                    $contracts->whereBetween('signature_date', [$start_date, $end_date]);
                }
            } elseif (isset($start_date)) {
                $contracts->whereDate('signature_date', '>', $start_date);
            } elseif (isset($end_date)) {
                $contracts->whereDate('signature_date', '<', $end_date);
            }

            return $contracts->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'contract_number' => 'required|string|unique:contracts',
                'client_id' => 'required|exists:clients,id',
                'tender_id' => 'nullable|exists:tenders,id',
                'contract_object' => 'required|string',
                'signature_date' => 'required|date',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'status' => 'required|string|in:Active,Completed,Canceled,AwaitingSignature,Renewing',
                'total_contract_value' => 'required|numeric',
                'payment_conditions' => 'required|string',
                'observations' => 'nullable|string',
                'products' => 'nullable|array',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,png,xls,xlsx|max:10240',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            $contract = Contract::create($validator->validated());

            if (isset($request->products)) {
                foreach ($request->products as $product) {
                    ContractProduct::create([
                        'contract_id' => $contract->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity'],
                    ]);
                }
            }

            $attachments = [];
            if ($request->attachments) {
                foreach ($request->attachments as $attachment) {
                    $path = $attachment->store('contract/attachments', 'public');
                    $fullPath = 'storage/' . $path;

                    $attachments[] = ContractFile::updateOrCreate(
                        [
                            'contract_id' => $contract->id,
                            'filename' => $attachment->getClientOriginalName(),
                            'path' => $fullPath,
                        ]
                    );
                }
            }

            Log::create([
                'description' => 'Contrato criado',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $contract];
        } catch (Exception $error) {
            DB::rollBack();

            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $contract_id)
    {
        try {
            $rules = [
                'contract_number' => 'required|string|unique:contracts,contract_number,'.$contract_id,
                'client_id' => 'required|exists:clients,id',
                'tender_id' => 'nullable|exists:tenders,id',
                'contract_object' => 'required|string',
                'signature_date' => 'required|date',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'status' => 'required|string|in:Active,Completed,Canceled,AwaitingSignature,Renewing',
                'total_contract_value' => 'required|numeric',
                'payment_conditions' => 'required|string',
                'observations' => 'nullable|string',
                'products' => 'nullable|array',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,png,xls,xlsx|max:10240',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $contractToUpdate = Contract::find($contract_id);

            if (! $contractToUpdate) {
                throw new Exception('Contrato não encontrado', 400);
            }

            $contractToUpdate->update($validator->validated());

            if (isset($request->products)) {
                foreach ($request->products as $product) {
                    ContractProduct::updateOrCreate(
                        [
                            'product_id' => $product['product_id'],
                            'contract_id' => $contractToUpdate->id,
                        ],
                        [
                            'quantity' => $product['quantity'],
                        ]);
                }
            }

            $attachments = [];
            if ($request->attachments) {
                foreach ($request->attachments as $attachment) {
                    $path = $attachment->store('contract/attachments', 'public');
                    $fullPath = 'storage/' . $path;

                    $attachments[] = ContractFile::updateOrCreate(
                        [
                            'contract_id' => $contractToUpdate->id,
                            'filename' => $attachment->getClientOriginalName(),
                            'path' => $fullPath,
                        ]
                    );
                }
            }

            Log::create([
                'description' => 'Contrato atualizado',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $contractToUpdate];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }

    public function delete($contract_id)
    {
        try {
            $contract = Contract::find($contract_id);

            if (! $contract) {
                throw new Exception('Contrato não encontrado');
            }

            $contractId = $contract->id;
            $contractName = $contract->name;
            $contract->delete();

            Log::create([
                'description' => 'Contrato deletado',
                'user_id' => Auth::user()->id,
                'request' => json_encode(['name' => $contractName]),
            ]);

            return ['status' => true, 'data' => ['supplierId' => $contractId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function createPayment($request)
    {
        try {
            $rules = [
                'amount_received' => 'required|numeric|min:0.01',
                'contract_id' => 'required|integer',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $payment = ContractPayment::create($validator->validated());

            return ['status' => true, 'data' => $payment];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function deletePayment($payment_id)
    {
        try {
            $payment = ContractPayment::find($payment_id);

            if (! $payment) {
                throw new Exception('Pagamento não encontrado');
            }

            $payment->delete();

            return ['status' => true, 'message' => 'Pagamento deletado com sucesso'];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function deleteContractProduct($contractProductId)
    {
        try {
            $contractProduct = ContractProduct::find($contractProductId);

            if (! $contractProduct) {
                throw new Exception('Produto não vinculado ao contrato');
            }

            $contractProduct->delete();

            return ['status' => true, 'message' => 'Produto desvinculado com sucesso'];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function deleteAttachment($attachmentId)
    {
        try {
            $attachment = ContractFile::find($attachmentId);

            if (! $attachment) {
                throw new Exception('Anexo não encontrado');
            }

            $attachmentId = $attachment->id;
            $attachment->delete();

            $filename = $attachment->filename;

            Log::create([
                'description' => 'Deletou um anexo',
                'user_id' => Auth::user()->id,
                'request' => json_encode(['name' => $filename]),
            ]);

            return ['status' => true, 'data' => ['attachmentId' => $attachmentId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
