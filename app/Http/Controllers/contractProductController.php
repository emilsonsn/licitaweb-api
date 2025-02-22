<?php

namespace App\Http\Controllers;

use App\Services\Contract\ContractProductService;
use Illuminate\Http\Request;

class contractProductController extends Controller
{
    private $contractProductService;

    public function __construct(ContractProductService $contractProductService)
    {
        $this->contractProductService = $contractProductService;
    }

    public function all()
    {
        $result = $this->contractProductService->all();

        return $this->response($result);
    }

    public function search(Request $request)
    {
        $result = $this->contractProductService->search($request);

        return $result;
    }

    public function getById($id)
    {
        $result = $this->contractProductService->getById($id);

        if ($result['status']) {
            $result['message'] = 'Produto de contrato encontrado';
        }

        return $this->response($result);
    }

    public function create(Request $request)
    {
        $result = $this->contractProductService->create($request);

        if ($result['status']) {
            $result['message'] = 'Operação realizada com sucesso';
        }

        return $this->response($result);
    }

    public function update(Request $request, $id)
    {
        $result = $this->contractProductService->update($request, $id);

        if ($result['status']) {
            $result['message'] = 'Produto de contrato atualizado com sucesso';
        }

        return $this->response($result);
    }

    public function delete($id)
    {
        $result = $this->contractProductService->delete($id);

        if ($result['status']) {
            $result['message'] = 'Produto de contrato excluído com sucesso';
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
