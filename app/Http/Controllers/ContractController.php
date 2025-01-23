<?php

namespace App\Http\Controllers;

use App\Services\Contract\ContractService;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    private $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    public function all()
    {
        $result = $this->contractService->all();

        return $this->response($result);
    }

    public function search(Request $request)
    {
        $result = $this->contractService->search($request);

        return $this->response($result);
    }

    public function create(Request $request)
    {
        $result = $this->contractService->create($request);

        if ($result['status']) {
            $result['message'] = 'Contrato criado com sucesso';
        }

        return $this->response($result);
    }

    public function update(Request $request, $id)
    {
        $result = $this->contractService->update($request, $id);

        if ($result['status']) {
            $result['message'] = 'Contrato atualizado com sucesso';
        }

        return $this->response($result);
    }

    public function delete($id)
    {
        $result = $this->contractService->delete($id);

        if ($result['status']) {
            $result['message'] = 'Contrato excluído com sucesso';
        }

        return $this->response($result);
    }

    public function createPayment(Request $request, $contractId)
    {
        $result = $this->contractService->createPayment($request);

        if ($result['status']) {
            $result['message'] = 'Pagamento adicionado com sucesso';
        }

        return $this->response($result);
    }

    public function deletePayment(int $paymentId)
    {
        $result = $this->contractService->deletePayment($paymentId);

        if ($result['status']) {
            $result['message'] = 'Pagamento removido com sucesso';
        }

        return $this->response($result);
    }

    public function deleteContractProduct(int $contractProductId)
    {
        $result = $this->contractService->deleteContractProduct($contractProductId);

        if ($result['status']) {
            $result['message'] = 'Produto desvinculado com sucesso';
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
