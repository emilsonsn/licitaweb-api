<?php

namespace App\Services\Modality;

use App\Models\Log;
use App\Models\Modality;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ModalityService
{
    public function all()
    {
        try {
            $modalities = Modality::get();
            return ['status' => true, 'data' => $modalities];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;

            $modalities = Modality::query();

            if (isset($search_term)) {
                $modalities->where('name', 'LIKE', "%{$search_term}%")                
                        ->orWhere('description', 'LIKE', "%{$search_term}%")
                        ->orWhere('external_id', 'LIKE', "%{$search_term}%");
            }

            return $modalities->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'description' => 'nullable|string',
                'external_id' => 'nullable|string',
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }
        
            $modality = Modality::create($validator->validated());

            Log::create([
                'description' => "Criou um modalidade",
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);
    
            return ['status' => true, 'data' => $modality];
        } catch (Exception $error) {
            DB::rollBack();
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $modality_id)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'description' => 'nullable|string',
                'external_id' => 'nullable|string',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $modality = Modality::find($modality_id);

            if (!$modality) {
                throw new Exception('Modalidade não encontrada', 400);
            }

            $modality->update($validator->validated());

            Log::create([
                'description' => "Atualizou um modalidade",
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $modality];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }

    public function delete($modality_id)
    {
        try {
            $modality = Modality::find($modality_id);

            if (!$modality) throw new Exception('Modalidade não encontrada');

            $modalityId = $modality->id;
            $modalityName = $modality->name;
            $modality->delete();

            Log::create([
                'description' => "Atualizou um modalidade",
                'user_id' => Auth::user()->id,
                'request' => json_encode(['name' => $modalityName]),
            ]);

            return ['status' => true, 'data' => ['modalityId' => $modalityId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}