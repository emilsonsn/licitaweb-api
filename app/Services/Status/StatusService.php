<?php

namespace App\Services\Status;

use App\Models\Status;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StatusService
{
    public function all()
    {
        try {
            $status = Status::get();
            return ['status' => true, 'data' => $status];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;

            $status = Status::query();

            if (isset($search_term)) {
                $status->where('name', 'LIKE', "%{$search_term}%");
            }

            return $status->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'color' => 'required|string',
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }
        
            $status = Status::create($validator->validated());
    
            return ['status' => true, 'data' => $status];
        } catch (Exception $error) {
            DB::rollBack();
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $status_id)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'color' => 'required|string',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $status = Status::find($status_id);

            if (!$status) {
                throw new Exception('Status não encontrado', 400);
            }

            $status->update($validator->validated());

            return ['status' => true, 'data' => $status];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }

    public function delete($status_id)
    {
        try {
            $status = Status::find($status_id);

            if (!isset($status)){
                throw new Exception('Status não encontrado', 400);
            }

            $statusId = $status->id;
            $status->delete();

            return ['status' => true, 'data' => ['statusId' => $statusId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}