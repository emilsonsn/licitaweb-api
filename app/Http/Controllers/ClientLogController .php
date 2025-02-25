<?php

namespace App\Http\Controllers;

use App\Services\Log\ClientLogService;
use Illuminate\Http\Request;

class ClientLogController extends Controller
{
    private $clientlogService;

    public function __construct(ClientLogService $clientlogService)
    {
        $this->clientlogService = $clientlogService;
    }

    public function all()
    {
        $result = $this->clientlogService->all();

        return $this->response($result);
    }

    public function search(Request $request)
    {
        $result = $this->clientlogService->search($request);

        return $result;
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
