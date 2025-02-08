<?php

namespace App\Http\Controllers;

use App\Services\Tender\TenderService;
use Illuminate\Http\Request;

class TenderController extends Controller
{
    private $tenderService;

    public function __construct(TenderService $tenderService)
    {
        $this->tenderService = $tenderService;
    }

    public function all()
    {
        $result = $this->tenderService->all();

        return $this->response($result);
    }

    public function search(Request $request)
    {
        $result = $this->tenderService->search($request);

        return $result;
    }

    public function getById($id)
    {
        $result = $this->tenderService->getById($id);

        if ($result['status']) {
            $result['message'] = 'Licitação encontrada';
        }

        return $this->response($result);
    }

    public function create(Request $request)
    {
        $result = $this->tenderService->create($request);

        if ($result['status']) {
            $result['message'] = 'Licitação criada com sucesso';
        }

        return $this->response($result);
    }

    public function update(Request $request, $id)
    {
        $result = $this->tenderService->update($request, $id);

        if ($result['status']) {
            $result['message'] = 'Licitação atualizada com sucesso';
        }

        return $this->response($result);
    }

    public function delete($id)
    {
        $result = $this->tenderService->delete($id);

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
