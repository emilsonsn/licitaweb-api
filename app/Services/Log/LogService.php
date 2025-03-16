<?php

namespace App\Services\Log;

use App\Models\Log;
use Exception;

class LogService
{
    public function all()
    {
        try {
            $logs = Log::get();

            return ['status' => true, 'data' => $logs];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $description = $request->description ?? null;
            $user_id = $request->user_id ?? null;
            $order = $request->input('order', 'ASC'); // Adicionando o parâmetro order, com valor padrão 'ASC'

            $logs = Log::with('user');

            if (isset($description)) {
                $logs->where('description', 'LIKE', "%{$description}%");
            }

            if (isset($user_id)) {
                $logs->where('user_id', $user_id);
            }

            // Verificar o valor de 'order' e ordenar os resultados
            if ($order === 'DESC') {
                $logs->orderBy('id', 'DESC');
            } else {
                $logs->orderBy('id', 'ASC'); // Caso o order não seja DESC, ordena de forma crescente (ASC)
            }

            return $logs->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

}
