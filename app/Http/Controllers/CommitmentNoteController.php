<?php

namespace App\Http\Controllers;

use App\Services\CommitmentNote\CommitmentNoteService;
use Illuminate\Http\Request;

class CommitmentNoteController extends Controller
{
    private $commitmentNoteService;

    public function __construct(CommitmentNoteService $commitmentNoteService)
    {
        $this->commitmentNoteService = $commitmentNoteService;
    }

    public function all()
    {
        $result = $this->commitmentNoteService->all();

        return $this->response($result);
    }

    public function search(Request $request)
    {
        $result = $this->commitmentNoteService->search($request);

        return $result;
    }

    public function create(Request $request)
    {
        $result = $this->commitmentNoteService->create($request);

        if ($result['status']) {
            $result['message'] = 'Nota de empenho criada com sucesso';
        }

        return $this->response($result);
    }

    public function update(Request $request, $id)
    {
        $result = $this->commitmentNoteService->update($request, $id);

        if ($result['status']) {
            $result['message'] = 'Nota de empenho atualizada com sucesso';
        }

        return $this->response($result);
    }

    public function delete(int $id)
    {
        $result = $this->commitmentNoteService->delete($id);

        if ($result['status']) {
            $result['message'] = 'Nota de empenho excluída com sucesso';
        }

        return $this->response($result);
    }

    public function deleteProduct(int $commitment_product_id)
    {
        $result = $this->commitmentNoteService->deleteProduct($commitment_product_id);

        if ($result['status']) {
            $result['message'] = 'Produto removido da nota de empenho com sucesso';
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
