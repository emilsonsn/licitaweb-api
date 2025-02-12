<?php

namespace App\Services\TenderItem;

use App\Models\Log;
use App\Models\Tender;
use App\Models\TenderItem;
use App\Models\Tenderproduct;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TenderItemService
{
    public function all()
    {
        try {
            $tenders = TenderProduct::get();

            return ['status' => true, 'data' => $tenders];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $tenders_id = $request->input('id');
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;

            $tenderItems = TenderProduct::with('products');

            if (!$tenders_id) {
                throw new Exception('Id do edital obrigatorio', 400);
            }

            $tenderItems->where('tender_id', $tenders_id);

            if (isset($search_term)) {
                $tenderItems->where('number', 'LIKE', "%{$search_term}%")
                    ->orWhere('organ', 'LIKE', "%{$search_term}%");
            }

            return $tenderItems->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function getById($id)
    {
        try {
            $tenderItems = TenderProduct::with('products')->find($id);

            if (!isset($tenderItems)) {
                throw new Exception('Produto do edital não encontrado');
            }

            return ['status' => true, 'data' => $tenderItems];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'tenderItens' => 'required|array',
                'tenderItens.*.product_id' => 'required|integer',
                'tenderItens.*.tender_id' => 'required|integer',
                'tenderItens.*.quantity' => 'required|integer',
            ];

            $data = $request->all();
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $tenderItems = []; // Variável corretamente definida

            if (isset($data['tenderItens']) && is_array($data['tenderItens'])) {
                foreach ($data['tenderItens'] as $item) {
                    $tenderItems[] = TenderProduct::updateOrCreate(
                        [
                            'product_id' => $item['product_id'],
                            'tender_id' => $item['tender_id'],
                        ],
                        [
                            'quantity' => $item['quantity'],
                        ]
                    );
                }
            }

            Log::create([
                'description' => 'Criou itens de edital',
                'user_id' => Auth::id(),
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $tenderItems];
        } catch (Exception $error) {
            DB::rollBack();
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $tenderItem_id)
    {
        try {

            $tenderItem = TenderProduct::find($tenderItem_id);

            $rules = [
                'product_id' => 'required|number',
                'tender_id' => 'required|number',
                'quantity' => 'required|number',
            ];

            $data = $request->all();
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $tenderItem->update($validator->validated());

            Log::create([
                'description' => 'Atualizou um item do edital',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);


            return ['status' => true, 'data' => $tenderItem];
        } catch (Exception $error) {
            DB::rollBack();

            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }


    public function delete($tenderItem_id)
    {
        try {
            $tenderItem = TenderProduct::find($tenderItem_id);

            if (!$tenderItem) {
                throw new Exception('item de edital não encontrada');
            }

            $tenderId = $tenderItem->id;
            $tenderObject = $tenderItem->object;
            $tenderItem->delete();

            Log::create([
                'description' => 'Deletou um item de edital',
                'user_id' => Auth::user()->id,
                'request' => json_encode(['object' => $tenderObject]),
            ]);

            return ['status' => true, 'data' => ['tenderItemId' => $tenderId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
