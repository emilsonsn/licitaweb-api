<?php

namespace App\Services\Contract;

use App\Models\ContractProduct;
use App\Models\Log;
use App\Models\Contract;
use App\Models\ContractPayment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContractService
{
    public function all()
    {
        try {
            $contracts = Contract::get();
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

            $contracts = Contract::query();

            if (isset($search_term)) {
                $contracts->where('contract_number', 'LIKE', "%{$search_term}%")
                          ->orWhere('contract_object', 'LIKE', "%{$search_term}%")
                          ->orWhere('status', 'LIKE', "%{$search_term}%");
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
                'products' => 'nullable|array'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            $contract = Contract::create($validator->validated());

            if(isset($request->products)){
                foreach($request->products as $product){
                    ContractProduct::create([
                        'contract_id' => $contract->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity']
                    ]);
                }
            }

            Log::create([
                'description' => "Contrato criado",
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
                'contract_number' => 'required|string|unique:contracts,contract_number,' . $contract_id,
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
                'products' => 'nullable|array'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $contractToUpdate = Contract::find($contract_id);

            if (!$contractToUpdate) {
                throw new Exception('Contrato não encontrado', 400);
            }

            $contractToUpdate->update($validator->validated());

            if(isset($request->products)){
                foreach($request->products as $product){
                    ContractProduct::updateOrCreate(
                        [
                            'product_id' => $product['product_id'],
                            'contract_id' => $contractToUpdate->id,
                        ],
                        [
                        'quantity' => $product['quantity']
                    ]);
                }
            }

            Log::create([
                'description' => "Contrato atualizado",
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

            if (!$contract) throw new Exception('Contrato não encontrado');

            $contractId = $contract->id;
            $contractName = $contract->name;
            $contract->delete();

            Log::create([
                'description' => "Contrato deletado",
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
                'contract_id' => 'required|integer'
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

            if (!$payment) throw new Exception('Pagamento não encontrado');

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

            if (!$contractProduct) throw new Exception('Produto não vinculado ao contrato');

            $contractProduct->delete();

            return ['status' => true, 'message' => 'Produto desvinculado com sucesso'];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}