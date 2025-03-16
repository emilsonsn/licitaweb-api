<?php

namespace App\Services\Contract;

use App\Models\ClientLog;
use App\Models\Contract;
use App\Models\ContractProduct;
use App\Models\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContractProductService
{
    public function all()
    {
        try {
            $contracts = ContractProduct::with('product')->get();

            return ['status' => true, 'data' => $contracts];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $contract_id = $request->input('id');

            $contracts = ContractProduct::with('product');

            if (!$contract_id) {
                throw new Exception('Id do edital obrigatorio', 400);
            }

            $contracts->where('contract_id', $contract_id);

            return $contracts->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function getById($id)
    {
        try {
            $contractProduct = ContractProduct::with('product')->find($id);

            if (!isset($contractProduct)) {
                throw new Exception('Produto do contrato não encontrado');
            }

            return ['status' => true, 'data' => $contractProduct];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }


    public function create($request)
    {
        try {
            $rules = [
                'contractProducts' => 'required|array',
                'contractProducts.*.product_id' => 'required|integer',
                'contractProducts.*.contract_id' => 'required|integer',
                'contractProducts.*.quantity' => 'required|integer',
                'contractProducts.*.sale_value' => 'required|decimal',
            ];

            $data = $request->all();
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            $contractProducts = ContractProduct::create($validator->validated());

            $contract_id = $data['contractProducts'][0]['contract_id'];
            $existingItems = ContractProduct::where('contract_id', $contract_id)->get()->keyBy('product_id');
            $contractProducts = [];
            $processedProductIds = [];

            foreach ($data['contractProducts'] as $item) {
                $processedProductIds[] = $item['product_id'];
                $contractProducts[] = ContractProduct::updateOrCreate(
                    [
                        'product_id' => $item['product_id'],
                        'contract_id' => $item['tender_id'],
                    ],
                    ['quantity' => $item['quantity']],
                    ['sale_value' => $item['sale_value']],
                );
            }

            $itemsToDelete = $existingItems->except($processedProductIds);
            foreach ($itemsToDelete as $item) {
                $item->delete();
            }

            $contract = Contract::find($request->contract_id);

            if (! $contract) {
                throw new Exception('Contrato não encontrado');
            }

            ClientLog::create([
                'description' => 'Produto de contrato vinculado ao cliente foi criado',
                'user_id' => Auth::user()->id,
                'client_id' => $contract->client_id,
                'request' => json_encode($request->all())
            ]);

            Log::create([
                'description' => 'Contrato criado',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $contractProducts];
        } catch (Exception $error) {
            DB::rollBack();

            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $contractProduct_id)
    {
        try {
            $contractProduct = ContractProduct::find($contractProduct_id);

            $rules = [
                'product_id' => 'required|number',
                'contract_id' => 'required|number',
                'quantity' => 'required|number',
                'sale_value' => 'required|decimal',
            ];

            $data = $request->all();
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $contractProduct->update($validator->validated());

            $contract = Contract::find($contractProduct->contract_id);

            if (! $contract) {
                throw new Exception('Contrato não encontrado');
            }

            ClientLog::create([
                'description' => 'Produto de contrato vinculado ao cliente foi atualizado',
                'user_id' => Auth::user()->id,
                'client_id' => $contract->client_id,
                'request' => json_encode($request->all())
            ]);

            Log::create([
                'description' => 'Atualizou um produto do contrato',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);


            return ['status' => true, 'data' => $contractProduct];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }

    public function delete($contractProduct_id)
    {
        try {
            $contractProduct = ContractProduct::find($contractProduct_id);

            if (! $contractProduct) {
                throw new Exception('Produto de contrato não encontrado');
            }

            $contractProductId = $contractProduct->id;
            $contractProduct->delete();

            $contract = Contract::find($contractProduct->contract_id);

            if (! $contract) {
                throw new Exception('Contrato não encontrado');
            }

            ClientLog::create([
                'description' => 'Produto de contrato vinculado ao cliente foi deletado',
                'user_id' => Auth::user()->id,
                'client_id' => $contract->client_id,
                'request' => json_encode(['id' => $contractProductId])
            ]);

            Log::create([
                'description' => 'Produto de contrato deletado',
                'user_id' => Auth::user()->id,
                'request' => json_encode(['id' => $contractProductId]),
            ]);

            return ['status' => true, 'data' => ['supplierId' => $contractProductId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
