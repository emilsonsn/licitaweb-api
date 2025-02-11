<?php

namespace App\Services\CommitmentNote;

use App\Models\Log;
use App\Models\CommitmentNote;
use App\Models\CommitmentNoteProduct;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CommitmentNoteService
{
    public function all()
    {
        try {
            $notes = CommitmentNote::with('products')->orderBy('id', 'desc')->get();

            return [
                'status' => true,
                'data' => $notes,
            ];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;
            $contract_id = $request->contract_id ?? null;

            $notes = CommitmentNote::with('products')->orderBy('id', 'desc');

            if (isset($search_term)) {
                $notes->where('number', 'LIKE', "%{$search_term}%");
            }

            if (isset($contract_id)) {
                $notes->where('contract_id', $contract_id);
            }

            $notes = $notes->paginate($perPage);

            return $notes;
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'contract_id' => ['required', 'integer', 'exists:contracts,id'],
                'number' => ['required', 'string', 'max:255'],
                'receipt_date' => ['required', 'date'],
                'purchase_term' => ['required', 'date'],
                'products' => ['required', 'array'],
                'products.*.product_id' => ['required', 'integer', 'exists:products,id'],
                'products.*.quantity' => ['required', 'integer', 'min:1'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);                
            }

            $validatedData = $validator->validated();

            $commitmentNote = CommitmentNote::create([
                'contract_id' => $validatedData['contract_id'],
                'number' => $validatedData['number'],
                'receipt_date' => $validatedData['receipt_date'],
                'purchase_term' => $validatedData['purchase_term'],
            ]);

            foreach ($validatedData['products'] as $product) {
                CommitmentNoteProduct::create([
                    'commitment_note_id' => $commitmentNote->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                ]);
            }

            return ['status' => true, 'data' => $commitmentNote->load('products')];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $id)
    {
        try {
            $rules = [
                'contract_id' => ['required', 'integer', 'exists:contracts,id'],
                'number' => ['required', 'string', 'max:255'],
                'receipt_date' => ['required', 'date'],
                'purchase_term' => ['required', 'date'],
                'products' => ['required', 'array'],
                'products.*.product_id' => ['required', 'integer', 'exists:products,id'],
                'products.*.quantity' => ['required', 'integer', 'min:1'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            $validatedData = $validator->validated();

            $commitmentNote = CommitmentNote::find($id);

            if (! $commitmentNote) {
                throw new Exception('Nota de empenho não encontrada');
            }

            $commitmentNote->update([
                'contract_id' => $validatedData['contract_id'],
                'number' => $validatedData['number'],
                'receipt_date' => $validatedData['receipt_date'],
                'purchase_term' => $validatedData['purchase_term'],
            ]);

            foreach ($validatedData['products'] as $product) {
                CommitmentNoteProduct::updateOrCreate(
                    [
                        'product_id' => $product['product_id'],
                        'commitment_note_id' => $commitmentNote->id,
                    ],
                    [
                        'quantity' => $product['quantity'],
                    ]);
            }

            return ['status' => true, 'data' => $commitmentNote->load('products')];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function delete($id)
    {
        try {
            $commitmentNote = CommitmentNote::find($id);

            if (! $commitmentNote) {
                throw new Exception('Nota de empenho não encontrada');
            }

            Log::create([
                'description' => 'Nota de empenho deletada',
                'user_id' => Auth::user()->id,
                'request' => json_encode($commitmentNote),
            ]);

            $commitmentNote->products()->delete();
            $commitmentNote->delete();

            return ['status' => true, 'data' => 'Nota de empenho deletada com sucesso'];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function deleteProduct($commitmentNoteProductId)
    {
        try {
            $commitmentNoteProduct = CommitmentNoteProduct::find($commitmentNoteProductId);

            if (!$commitmentNoteProduct) {
                throw new Exception('Produto não encontrado');
            }

            $commitmentNoteProduct->delete();

            return ['status' => true, 'data' => 'Produto removido da nota de empenho com sucesso'];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}