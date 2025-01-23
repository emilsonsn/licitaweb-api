<?php

namespace App\Http\Controllers;

use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function all()
    {
        $result = $this->notificationService->all();

        return $this->response($result);
    }

    public function search(Request $request)
    {
        $result = $this->notificationService->search($request);

        return $result;
    }

    public function create(Request $request)
    {
        $result = $this->notificationService->create($request);

        if ($result['status']) {
            $result['message'] = 'Notificação criada com sucesso';
        }

        return $this->response($result);
    }

    public function update(Request $request, $id)
    {
        $result = $this->notificationService->update($request, $id);

        if ($result['status']) {
            $result['message'] = 'Notificação atualizada com sucesso';
        }

        return $this->response($result);
    }

    public function delete($id)
    {
        $result = $this->notificationService->delete($id);

        if ($result['status']) {
            $result['message'] = 'Notificação excluída com sucesso';
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
