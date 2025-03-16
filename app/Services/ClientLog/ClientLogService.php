<?php

namespace App\Services\ClientLog;

use App\Models\ClientLog;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientLogService
{
    public function all(): array
    {
        try {
            $logs = ClientLog::get();

            return ['status' => true, 'data' => $logs];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request): LengthAwarePaginator|array
    {
        try {
            $perPage = $request->input('take', 10);
            $description = $request->description ?? null;
            $user_id = $request->user_id ?? null;
            $client_id = $request->client_id ?? null;

            $logs = ClientLog::with('user', 'client');

            if (isset($search_term)) {
                $logs->where('description', 'LIKE', "%{$description}%");
            }

            if (isset($user_id)) {
                $logs->where('user_id', $user_id);
            }

            if (isset($client_id)) {
                $logs->where('client_id', $client_id);
            }

            return $logs->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
