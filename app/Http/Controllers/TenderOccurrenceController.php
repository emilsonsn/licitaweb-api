<?php

namespace App\Http\Controllers;

use App\Services\TenderOccurrence\TenderOccurrenceService;
use Illuminate\Http\Request;

class TenderOccurrenceController extends Controller
{
    private $tenderOccurrenceService;

    public function __construct(TenderOccurrenceService $tenderOccurrenceService) {
        $this->tenderOccurrenceService = $tenderOccurrenceService;
    }

    public function search(Request $request) {
        $result = $this->tenderOccurrenceService->search($request);
        return $result;
    }

    public function create(Request $request) {
        $result = $this->tenderOccurrenceService->create($request);

        if ($result['status']) {
            $result['message'] = "Ocorrência criada com sucesso";
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
