<?php

namespace App\Services\TenderItem;

use App\Models\ClientLog;
use App\Models\Log;
use App\Models\Tender;
use App\Models\TenderLog;
use App\Models\TenderProduct;
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

            $tenderItems = TenderProduct::with('product');

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
            $tenderItems = TenderProduct::with('product')->find($id);

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

            $tender_id = $data['tenderItens'][0]['tender_id'];
            $existingItems = TenderProduct::where('tender_id', $tender_id)->get()->keyBy('product_id');
            $tenderItems = [];
            $processedProductIds = [];

            foreach ($data['tenderItens'] as $item) {
                $processedProductIds[] = $item['product_id'];
                $tenderItems[] = TenderProduct::updateOrCreate(
                    [
                        'product_id' => $item['product_id'],
                        'tender_id' => $item['tender_id'],
                    ],
                    ['quantity' => $item['quantity']]
                );
            }

            $itemsToDelete = $existingItems->except($processedProductIds);
            foreach ($itemsToDelete as $item) {
                $item->delete();
            }

            $tender = Tender::find($tender_id);

            if (! isset($tender)) {
                throw new Exception('Licitação não encontrado');
            }

            if(isset($tender->client_id)){
                ClientLog::create([
                    'description' => 'Criou, atualizou ou removeu itens do edital vinculado ao cliente',
                    'user_id' => Auth::user()->id,
                    'client_id' => $tender->client_id,
                    'request' => json_encode($request->all())
                ]);
            }

            if($tender->status == 3){
                TenderLog::create([
                    'description' => 'Criou, atualizou ou removeu itens do edital errematado',
                    'user_id' => Auth::user()->id,
                    'tender_id' => $tender->id,
                    'request' => json_encode($request->all())
                ]);
            }

            Log::create([
                'description' => 'Criou, atualizou ou removeu itens do edital',
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

            $tender = Tender::find($tenderItem->tender_id);

            if (! isset($tender)) {
                throw new Exception('Licitação não encontrado');
            }

            if(isset($tender->client_id)){
                ClientLog::create([
                    'description' => 'Item do edital vinculado ao cliente atualizado',
                    'user_id' => Auth::user()->id,
                    'client_id' => $tender->client_id,
                    'request' => json_encode($request->all())
                ]);
            }

            if($tender->status == 3){
                TenderLog::create([
                    'description' => 'Item do edital errematado atualizado',
                    'user_id' => Auth::user()->id,
                    'tender_id' => $tender->id,
                    'request' => json_encode($request->all())
                ]);
            }

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

            $tender = Tender::find($tenderItem->tender_id);

            if (! isset($tender)) {
                throw new Exception('Licitação não encontrado');
            }

            $tenderId = $tenderItem->id;
            $tenderObject = $tenderItem->object;
            $tenderItem->delete();

            if(isset($tender->client_id)){
                ClientLog::create([
                    'description' => 'Item do edital vinculado ao cliente foi deletado',
                    'user_id' => Auth::user()->id,
                    'client_id' => $tender->client_id,
                    'request' => json_encode(['object' => $tenderObject])
                ]);
            }

            if($tender->status == 3){
                TenderLog::create([
                    'description' => 'Item do edital errematado deletado',
                    'user_id' => Auth::user()->id,
                    'tender_id' => $tender->id,
                    'request' => json_encode(['object' => $tenderObject])
                ]);
            }

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
