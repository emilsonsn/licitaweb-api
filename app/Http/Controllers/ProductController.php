<?php

namespace App\Http\Controllers;

use App\Services\Product\ProductService;
use App\Services\Supplier\SupplierService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductService $productService) {
        $this->productService = $productService;
    }

    public function all() {
        $result = $this->productService->all();
        return $this->response($result);
    }

    public function search(Request $request) {
        $result = $this->productService->search($request);
        return $this->response($result);
    }

    public function create(Request $request) {
        $result = $this->productService->create($request);

        if ($result['status']) {
            $result['message'] = "Produto criado com sucesso";
        }

        return $this->response($result);
    }

    public function update(Request $request, $id) {
        $result = $this->productService->update($request, $id);

        if ($result['status']) {
            $result['message'] = "Produto atualizado com sucesso";
        }

        return $this->response($result);
    }

    public function delete($id) {
        $result = $this->productService->delete($id);

        if ($result['status']) {
            $result['message'] = "Produto deletado com sucesso";
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
