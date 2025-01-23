<?php

namespace App\Services\SupplierOccurrence;

use App\Models\SupplierOccurrence;
use App\Models\SupplierOccurrenceFile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupplierOccurrenceService
{
    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term;
            $supplier_id = $request->supplier_id;
            $user_id = $request->user_id;

            $supplierOccurrence = SupplierOccurrence::with('files')->orderBy('id', 'desc');

            if (isset($search_term)) {
                $supplierOccurrence->where('title', 'LIKE', "%{$search_term}%")
                    ->orWhere('description', 'LIKE', "%{$search_term}%");
            }

            if (isset($supplier_id)) {
                $supplierOccurrence->where('supplier_id', $supplier_id);
            }

            if (isset($user_id)) {
                $supplierOccurrence->where('user_id', $user_id);
            }

            $supplierOccurrence = $supplierOccurrence->paginate($perPage);

            return $supplierOccurrence;
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'supplier_id' => ['required', 'integer'],
                'files' => ['nullable', 'array'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $data = $validator->validated();

            // $data['user_id'] = Auth::user()->id;

            $supplierOccurrence = SupplierOccurrence::create($data);

            $files = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('tenders/occurrences', 'public');

                    $files[] = SupplierOccurrenceFile::create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'client_occurrence_id' => $supplierOccurrence->id,
                    ]);
                }
            }

            $supplierOccurrence['files'] = $files;

            return ['status' => true, 'data' => $supplierOccurrence];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
