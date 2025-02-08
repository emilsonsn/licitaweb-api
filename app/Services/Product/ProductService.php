<?php

namespace App\Services\Product;

use App\Models\Log;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductOccurrence;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductService
{
    public function all()
    {
        try {
            $products = Product::with('attachments')->get();

            return ['status' => true, 'data' => $products];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;

            $products = Product::with('attachments');

            if (isset($search_term)) {
                $products->where('name', 'LIKE', "%{$search_term}%")
                    ->orWhere('sku', 'LIKE', "%{$search_term}%")
                    ->orWhere('category', 'LIKE', "%{$search_term}%");
            }

            return $products->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function historical($request)
    {
        try {
            $product_id = $request->input('id');
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;

            $products = ProductOccurrence::query();

            if (!$product_id) {
                throw new Exception('Id de produto obrigatorio', 400);
            }

            $products->where('product_id', $product_id);

            if (isset($search_term)) {
                $products->where('name', 'LIKE', "%{$search_term}%")
                    ->orWhere('sku', 'LIKE', "%{$search_term}%")
                    ->orWhere('category', 'LIKE', "%{$search_term}%");
            }

            return $products->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'sku' => 'required|string|unique:products',
                'category' => 'required|string',
                'detailed_description' => 'nullable|string',
                'size' => 'nullable|string',
                'technical_information' => 'nullable|string',
                'brand' => 'required|string',
                'origin' => 'required|string',
                'model' => 'nullable|string',
                'purchase_cost' => 'required|numeric',
                'freight' => 'required|numeric',
                'taxes_fees' => 'required|numeric',
                'profit_margin' => 'required|numeric',
                'supplier_id' => 'required|exists:suppliers,id',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,png,xls,xlsx|max:10240',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $product = Product::create($validator->validated());

            $attachments = [];
            if ($request->attachments) {
                foreach ($request->attachments as $attachment) {
                    $path = $attachment->store('product/attachments', 'public');
                    $fullPath = 'storage/' . $path;

                    $attachments[] = ProductFile::create([
                        'product_id' => $product->id,
                        'filename' => $attachment->getClientOriginalName(),
                        'path' => $fullPath,
                    ]);
                }
            }

            Log::create([
                'description' => 'Created a product',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $product];
        } catch (Exception $error) {
            DB::rollBack();

            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $product_id)
    {
        try {
            $product = Product::where('sku', $request->sku)->first();

            if (! $product) {
                $product = Product::find($product_id);
                if (! $product) {
                    $product = Product::find($product_id);
                    throw new Exception('Product not found', 400);
                }
            }

            if ($product->id != $product_id) {
                throw new Exception('SKU cadastrado em outro produto', 400);
            }

            $rules = [
                'name' => 'required|string',
                'sku' => 'required|string',
                'category' => 'required|string',
                'detailed_description' => 'nullable|string',
                'size' => 'nullable|string',
                'technical_information' => 'nullable|string',
                'brand' => 'required|string',
                'origin' => 'required|string',
                'model' => 'nullable|string',
                'purchase_cost' => 'required|numeric',
                'freight' => 'required|numeric',
                'taxes_fees' => 'required|numeric',
                'profit_margin' => 'required|numeric',
                'supplier_id' => 'required|exists:suppliers,id',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,png,xls,xlsx|max:10240',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            if ($product->purchase_cost != $request->purchase_cost) {
                $user = User::find(Auth::user()->id);
                ProductOccurrence::create([
                    'title' => 'Alteração de custo de aquisição',
                    'description' => $user->name . ' alterou o valor de custo de ' . $product->purchase_cost . ' para ' . $request->purchase_cost,
                    'product_id' => $product_id
                ]);
            }

            $product->update($validator->validated());

            $attachments = [];
            if ($request->attachments) {
                foreach ($request->attachments as $attachment) {
                    $path = $attachment->store('product/attachments', 'public');
                    $fullPath = 'storage/' . $path;

                    $attachments[] = ProductFile::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'filename' => $attachment->getClientOriginalName(),
                            'path' => $fullPath,
                        ]
                    );
                }
            }

            Log::create([
                'description' => 'Updated a product',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $product];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }

    public function delete($product_id)
    {
        try {
            $product = Product::find($product_id);

            if (! $product) {
                throw new Exception('Product not found');
            }

            $productId = $product->id;
            $productName = $product->name;
            $product->delete();

            Log::create([
                'description' => 'Deleted a product',
                'user_id' => Auth::user()->id,
                'request' => json_encode(['name' => $productName]),
            ]);

            return ['status' => true, 'data' => ['productId' => $productId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function deleteAttachment($attachmentId)
    {
        try {
            $attachment = ProductFile::find($attachmentId);

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
