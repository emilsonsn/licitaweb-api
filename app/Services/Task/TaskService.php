<?php

namespace App\Services\Task;

use App\Models\Log;
use App\Models\TenderTask;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskService
{
    public function all()
    {
        try {
            $tasks = TenderTask::with('user')->get();
            return ['status' => true, 'data' => $tasks];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;
            $due_date = $request->due_date ?? null;
            $tender_id = $request->tender_id ?? null;
            $user_id = $request->user_id ?? null;
            $status = $request->status ?? null;

            $tasks = TenderTask::query();

            if ($search_term) {
                $tasks->where('name', 'LIKE', "%{$search_term}%")
                    ->orWhere('description', 'LIKE', "%{$search_term}%");
            }

            if ($due_date) {
                $tasks->whereDate('due_date', $search_term);
            }

            if ($tender_id) {
                $tasks->where('tender_id', $tender_id);
            }

            if ($user_id) {
                $tasks->where('user_id', $user_id);
            }

            if($status){
                $tasks->where('status', $status);
            }

            return $tasks->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'due_date' => 'required|date',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:Pending,InProgress,Completed',
                'tender_id' => 'required|integer',
                'user_id' => 'nullable|integer',
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            $data = $validator->validated();

            $data['user_id'] = $data['user_id'] ?? Auth::user()->id;
        
            $task = TenderTask::create($data);

            Log::create([
                'description' => "Criou um tarefa",
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);
    
            return ['status' => true, 'data' => $task];
        } catch (Exception $error) {
            DB::rollBack();
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $task_id)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'due_date' => 'required|date',
                'status' => 'nullable|string|in:Pending,InProgress,Completed',
                'description' => 'nullable|string',
                'tender_id' => 'required|integer',
                'user_id' => 'nullable|integer',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $task = TenderTask::find($task_id);

            if (!$task) {
                throw new Exception('Tarefa não encontrada', 400);
            }

            $data = $validator->validated();

            $data['user_id'] = $data['user_id'] ?? Auth::user()->id;

            $task->update($data);

            Log::create([
                'description' => "Atualizou um tarefa",
                'user_id' => Auth::user()->id,
                'request' => json_encode($request->all()),
            ]);

            return ['status' => true, 'data' => $task];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }

    public function delete($task_id)
    {
        try {
            $task = TenderTask::find($task_id);

            if (!$task) throw new Exception('Tarefa não encontrada');

            $taskId = $task->id;
            $taskName = $task->name;
            $task->delete();

            Log::create([
                'description' => "Apagou um tarefa",
                'user_id' => Auth::user()->id,
                'request' => json_encode(['Nome' => $taskName]),
            ]);

            return ['status' => true, 'data' => ['taskId' => $taskId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
