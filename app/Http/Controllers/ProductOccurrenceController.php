<?php

namespace App\Http\Controllers;

use App\Services\ProductOccurrence\ProductOccurrenceService;
use Illuminate\Http\Request;

class ProductOccurrenceController extends Controller
{
    private $productOccurrenceService;

    public function __construct(ProductOccurrenceService $productOccurrenceService) {
        $this->productOccurrenceService = $productOccurrenceService;
    }

    public function search(Request $request) {
        $result = $this->productOccurrenceService->search($request);
        return $result;
    }

    public function create(Request $request) {
        $result = $this->productOccurrenceService->create($request);

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
