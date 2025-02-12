<?php

namespace App\Http\Controllers;

use App\Services\TenderItem\TenderItemService;
use Illuminate\Http\Request;

class TenderItemController extends Controller
{
    private $tenderItemService;

    public function __construct(TenderItemService $tenderItemService)
    {
        $this->tenderItemService = $tenderItemService;
    }

    public function all()
    {
        $result = $this->tenderItemService->all();

        return $this->response($result);
    }

    public function search(Request $request)
    {
        $result = $this->tenderItemService->search($request);

        return $result;
    }

    public function getById($id)
    {
        $result = $this->tenderItemService->getById($id);

        if ($result['status']) {
            $result['message'] = 'Licitação encontrada';
        }

        return $this->response($result);
    }

    public function create(Request $request)
    {
        $result = $this->tenderItemService->create($request);

        if ($result['status']) {
            $result['message'] = 'Licitação criada com sucesso';
        }

        return $this->response($result);
    }

    public function update(Request $request, $id)
    {
        $result = $this->tenderItemService->update($request, $id);

        if ($result['status']) {
            $result['message'] = 'Licitação atualizada com sucesso';
        }

        return $this->response($result);
    }

    public function delete($id)
    {
        $result = $this->tenderItemService->delete($id);

        if ($result['status']) {
            $result['message'] = 'Licitação excluída com sucesso';
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
