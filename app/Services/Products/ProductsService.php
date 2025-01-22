<?php

namespace App\Services\Supplier;

use App\Models\Log;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductsService
{
    public function all()
    {
        try {
            $suppliers = Supplier::get();
            return ['status' => true, 'data' => $suppliers];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;

            $suppliers = Supplier::query();

            if (isset($search_term)) {
                $suppliers->where('name', 'LIKE', "%{$search_term}%")
                          ->orWhere('cpf_or_cnpj', 'LIKE', "%{$search_term}%")
                          ->orWhere('email', 'LIKE', "%{$search_term}%");
            }

            return $suppliers->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'cpf_or_cnpj' => 'required|string|unique:suppliers',
                'state_registration' => 'nullable|string',
                'street' => 'required|string',
                'number' => 'required|string',
                'complement' => 'nullable|string',
                'neighborhood' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'zip_code' => 'required|string',
                'landline_phone' => 'nullable|string',
                'mobile_phone' => 'required|string',
                'email' => 'required|email|unique:suppliers',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            $supplier = Supplier::create($validator->validated());

            Log::create([
                'description' => "Created a supplier",
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $supplier];
        } catch (Exception $error) {
            DB::rollBack();
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $supplier_id)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'cpf_or_cnpj' => 'required|string|unique:suppliers,cpf_or_cnpj,' . $supplier_id,
                'state_registration' => 'nullable|string',
                'street' => 'required|string',
                'number' => 'required|string',
                'complement' => 'nullable|string',
                'neighborhood' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'zip_code' => 'required|string',
                'landline_phone' => 'nullable|string',
                'mobile_phone' => 'required|string',
                'email' => 'required|email|unique:suppliers,email,' . $supplier_id,
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $supplier = Supplier::find($supplier_id);

            if (!$supplier) {
                throw new Exception('Supplier not found', 400);
            }

            $supplier->update($validator->validated());

            Log::create([
                'description' => "Updated a supplier",
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $supplier];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }

    public function delete($supplier_id)
    {
        try {
            $supplier = Supplier::find($supplier_id);

            if (!$supplier) throw new Exception('Supplier not found');

            $supplierId = $supplier->id;
            $supplierName = $supplier->name;
            $supplier->delete();

            Log::create([
                'description' => "Deleted a supplier",
                'user_id' => Auth::user()->id,
                'request' => json_encode(['name' => $supplierName]),
            ]);

            return ['status' => true, 'data' => ['supplierId' => $supplierId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
