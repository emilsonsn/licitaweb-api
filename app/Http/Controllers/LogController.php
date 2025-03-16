<?php

namespace App\Http\Controllers;

use App\Services\Log\LogService;
use Illuminate\Http\Request;

class LogController extends Controller
{
    private $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function all()
    {
        $result = $this->logService->all();

        return $this->response($result);
    }

    public function search(Request $request)
    {
        $result = $this->logService->search($request);

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
