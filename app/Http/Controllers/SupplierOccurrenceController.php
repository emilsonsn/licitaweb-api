<?php

namespace App\Http\Controllers;

use App\Services\SupplierOccurrence\SupplierOccurrenceService;
use App\Services\TenderOccurrence\TenderOccurrenceService;
use Illuminate\Http\Request;

class SupplierOccurrenceController extends Controller
{
    private $supplierOccurrenceService;

    public function __construct(SupplierOccurrenceService $supplierOccurrenceService) {
        $this->supplierOccurrenceService = $supplierOccurrenceService;
    }

    public function search(Request $request) {
        $result = $this->supplierOccurrenceService->search($request);
        return $result;
    }

    public function create(Request $request) {
        $result = $this->supplierOccurrenceService->create($request);

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
