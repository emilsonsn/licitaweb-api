<?php

namespace App\Http\Controllers;

use App\Services\Tender\TenderService;
use Illuminate\Http\Request;

class TenderController extends Controller
{
    private $tenderService;

    public function __construct(TenderService $tenderService) {
        $this->tenderService = $tenderService;
    }

    public function all() {
        $result = $this->tenderService->all();
        return $this->response($result);
    }

    public function search(Request $request) {
        $result = $this->tenderService->search($request);
        return $this->response($result);
    }

    public function create(Request $request) {
        $result = $this->tenderService->create($request);

        if ($result['status']) {
            $result['message'] = "Licitação criada com sucesso";
        }

        return $this->response($result);
    }

    public function update(Request $request, $id) {
        $result = $this->tenderService->update($request, $id);

        if ($result['status']) {
            $result['message'] = "Licitação atualizada com sucesso";
        }

        return $this->response($result);
    }

    public function updateStatus($tender_id, Request $request) {
        $new_status_id = $request->input('status_id');
        $position = $request->input('position');

        $result = $this->tenderService->updateStatus($tender_id, $new_status_id, $position);

        if ($result['status']) {
            $result['message'] = "Status da licitação atualizado com sucesso";
        }

        return $this->response($result);
    }

    public function delete($id) {
        $result = $this->tenderService->delete($id);

        if ($result['status']) {
            $result['message'] = "Licitação excluída com sucesso";
        }

        return $this->response($result);
    }

    public function deleteAttachment($attachmentId) {
        $result = $this->tenderService->deleteAttachment($attachmentId);

        if ($result['status']) {
            $result['message'] = "Anexo excluído com sucesso";
        }

        return $this->response($result);
    }

    public function deleteItem($itemId) {
        $result = $this->tenderService->deleteItem($itemId);

        if ($result['status']) {
            $result['message'] = "Item excluído com sucesso";
        }

        return $this->response($result);
    }

    public function deleteTask($taskId) {
        $result = $this->tenderService->deleteTask($taskId);

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
