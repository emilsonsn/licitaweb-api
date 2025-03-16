<?php

namespace App\Http\Controllers;

use App\Services\ClientLog\ClientLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientLogController extends Controller
{
    private ClientLogService $clientLogService;

    public function __construct(ClientLogService $clientLogService)
    {
        $this->clientLogService = $clientLogService;
    }

    public function all(): JsonResponse
    {
        $result = $this->clientLogService->all();

        return $this->response($result);
    }

    public function search(Request $request): LengthAwarePaginator|array
    {
        return $this->clientLogService->search($request);
    }

    private function response($result): JsonResponse
    {
        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'] ?? null,
            'data' => $result['data'] ?? null,
            'error' => $result['error'] ?? null,
        ], $result['statusCode'] ?? 200);
    }
}
