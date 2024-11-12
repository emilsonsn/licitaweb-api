<?php

namespace App\Http\Controllers;

use App\Services\Status\StatusService;
use App\Services\Task\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private $tenderTaskService;

    public function __construct(TaskService $tenderTaskService) {
        $this->tenderTaskService = $tenderTaskService;
    }

    public function all() {
        $result = $this->tenderTaskService->all();
        return $this->response($result);
    }

    public function search(Request $request) {
        $result = $this->tenderTaskService->search($request);
        return $result;
    }

    public function create(Request $request) {
        $result = $this->tenderTaskService->create($request);

        if ($result['status']) {
            $result['message'] = "Tarefa criada com sucesso";
        }

        return $this->response($result);
    }

    public function update(Request $request, $id) {
        $result = $this->tenderTaskService->update($request, $id);

        if ($result['status']) {
            $result['message'] = "Tarefa atualizada com sucesso";
        }

        return $this->response($result);
    }

    public function delete($id) {
        $result = $this->tenderTaskService->delete($id);

        if ($result['status']) {
            $result['message'] = "Tarefa excluída com sucesso";
        }

        return $this->response($result);
    }

    private function response($result) {
        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'] ?? null,
            'data' => $result['data'] ?? null,
            'error' => $result['error'] ?? null
        ], $result['statusCode'] ?? 200);
    }
}
