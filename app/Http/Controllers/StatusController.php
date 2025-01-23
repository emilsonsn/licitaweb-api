<?php

namespace App\Http\Controllers;

use App\Services\Status\StatusService;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    private $statusService;

    public function __construct(StatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    public function all()
    {
        $result = $this->statusService->all();

        return $this->response($result);
    }

    public function search(Request $request)
    {
        $result = $this->statusService->search($request);

        return $result;
    }

    public function create(Request $request)
    {
        $result = $this->statusService->create($request);

        if ($result['status']) {
            $result['message'] = 'Etapa criada com sucesso';
        }

        return $this->response($result);
    }

    public function update(Request $request, $id)
    {
        $result = $this->statusService->update($request, $id);

        if ($result['status']) {
            $result['message'] = 'Etapa atualizada com sucesso';
        }

        return $this->response($result);
    }

    public function delete($id)
    {
        $result = $this->statusService->delete($id);

        if ($result['status']) {
            $result['message'] = 'Etapa excluída com sucesso';
        }

        return $this->response($result);
    }

    private function response($result)
    {
        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'] ?? null,
            'data' => $result['data'] ?? null,
            'error' => $result['error'] ?? null,
        ], $result['statusCode'] ?? 200);
    }
}
