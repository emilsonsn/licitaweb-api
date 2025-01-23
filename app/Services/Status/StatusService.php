<?php

namespace App\Services\Status;

use App\Models\Log;
use App\Models\Status;
use Exception;
use Illuminate\Support\Facades\Auth;
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

            Log::create([
                'description' => 'Criou um status',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request),
            ]);

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

            if (! $status) {
                throw new Exception('Etapa não encontrada', 400);
            }

            $status->update($validator->validated());

            Log::create([
                'description' => 'Atualizou um status',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request),
            ]);

            return ['status' => true, 'data' => $status];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }

    public function delete($status_id)
    {
        try {
            $status = Status::find($status_id);

            if (! isset($status)) {
                throw new Exception('Etapa não encontrado', 400);
            }

            if ($status->tenderStatuses()->count()) {
                throw new Exception('Não é possível deletar Etapa com editais', 400);
            }

            if (Status::count() == 1) {
                throw new Exception('Não é possível apagar todas as etapas', 400);
            }

            $statusId = $status->id;
            $statusName = $status->name;
            $status->delete();

            Log::create([
                'description' => 'Deletou um status',
                'user_id' => Auth::user()->id,
                'request' => json_encode(['name' => $statusName]),
            ]);

            return ['status' => true, 'data' => ['statusId' => $statusId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
