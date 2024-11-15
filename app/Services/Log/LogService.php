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

            $logs = Log::with('user');

            if (isset($search_term)) {
                $logs->where('description', 'LIKE', "%{$description}%");
            }

            if (isset($user_id)) {
                $logs->where('user_id', $user_id);
            }

            return $logs->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
