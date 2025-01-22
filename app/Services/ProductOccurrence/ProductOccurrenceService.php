<?php

namespace App\Services\ProductOccurrence;

use App\Models\ProductOccurrence;
use App\Models\ProductOccurrenceFile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductOccurrenceService
{

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term;
            $product_id = $request->product_id;
            $user_id = $request->user_id;

            $productOccurrence = ProductOccurrence::with('files')->orderBy('id', 'desc');

            if(isset($search_term)){
                $productOccurrence->where('title', 'LIKE', "%{$search_term}%")
                    ->orWhere('description', 'LIKE', "%{$search_term}%");
            }

            if(isset($product_id)){
                $productOccurrence->where('product_id', $product_id);
            }

            if(isset($user_id)){
                $productOccurrence->where('user_id', $user_id);
            }

            $productOccurrence = $productOccurrence->paginate($perPage);

            return $productOccurrence;
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
                'product_id' => ['required', 'integer'],
                'files' => ['nullable', 'array']
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];;
            }

            $data = $validator->validated();

            $data['user_id'] = Auth::user()->id;

            $productOccurrence = ProductOccurrence::create($data);

            $files = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('tenders/occurrences', 'public');
    
                    $files[] = ProductOccurrenceFile::create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'client_occurrence_id' => $productOccurrence->id,
                    ]);
                }
            }

            $productOccurrence['files'] = $files;

            return ['status' => true, 'data' => $productOccurrence];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}