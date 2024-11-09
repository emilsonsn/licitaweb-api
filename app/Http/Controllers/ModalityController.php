<?php

namespace App\Http\Controllers;

use App\Services\Modality\ModalityService;
use Illuminate\Http\Request;

class ModalityController extends Controller
{
    private $modalityService;

    public function __construct(ModalityService $modalityService) {
        $this->modalityService = $modalityService;
    }

    public function all() {
        $result = $this->modalityService->all();
        return $this->response($result);
    }

    public function search(Request $request) {
        $result = $this->modalityService->search($request);
        return $this->response($result);
    }

    public function create(Request $request) {
        $result = $this->modalityService->create($request);

        if ($result['status']) {
            $result['message'] = "Modalidade criada com sucesso";
        }

        return $this->response($result);
    }

    public function update(Request $request, $id) {
        $result = $this->modalityService->update($request, $id);

        if ($result['status']) {
            $result['message'] = "Modalidade atualizada com sucesso";
        }

        return $this->response($result);
    }

    public function delete($id) {
        $result = $this->modalityService->delete($id);

        if ($result['status']) {
            $result['message'] = "Modalidade excluída com sucesso";
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
