<?php

namespace App\Http\Controllers;

use App\Services\ClientOccurrence\ClientOccurrenceService;
use Illuminate\Http\Request;

class ClientOccurrenceController extends Controller
{
    private $clientOccurrenceService;

    public function __construct(ClientOccurrenceService $clientOccurrenceService)
    {
        $this->clientOccurrenceService = $clientOccurrenceService;
    }

    public function search(Request $request)
    {
        $result = $this->clientOccurrenceService->search($request);

        return $result;
    }

    public function create(Request $request)
    {
        $result = $this->clientOccurrenceService->create($request);

        if ($result['status']) {
            $result['message'] = 'Ocorrência criada com sucesso';
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
