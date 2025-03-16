<?php

namespace App\Services\Notification;

use App\Models\Log;
use App\Models\Notification;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationService
{
    public function all()
    {
        try {
            $notifications = Notification::get();

            return ['status' => true, 'data' => $notifications];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;
            $viewed = $request->viewed ?? null;


            $notifications = Notification::query();

            if (isset($search_term)) {
                $notifications->where('description', 'LIKE', "%{$search_term}%");
            }
            if(isset($viewed)){
                $notifications->where('viewed', $viewed);
            }

            return $notifications->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'description' => ['required', 'string', 'max:255'],
                'message' => ['required', 'string', 'max:255'],
                'datetime' => ['required', 'date_format:Y-m-d H:i:s'],
                'tender_id' => ['nullable', 'integer'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            $data = $validator->validated();

            $data['user_id'] = Auth::user()->id;

            $notification = Notification::create($data);

            Log::create([
                'description' => 'Criou uma notificação',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request),
            ]);

            return ['status' => true, 'data' => $notification];
        } catch (Exception $error) {
            DB::rollBack();

            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $status_id)
    {
        try {
            $rules = [
                'description' => ['required', 'string', 'max:255'],
                'message' => ['required', 'string', 'max:255'],
                'datetime' => ['required', 'date_format:Y-m-d H:i:s'],
                'tender_id' => ['nullable', 'integer'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $notificationToUpdate = Notification::find($status_id);

            if (! $notificationToUpdate) {
                throw new Exception('Notificação não encontrada', 400);
            }
            $data = $validator->validated();
            $data['user_id'] = Auth::user()->id;

            $notificationToUpdate->update($data);

            Log::create([
                'description' => 'Atualizou uma notificação',
                'user_id' => Auth::user()->id,
                'request' => json_encode($request),
            ]);

            return ['status' => true, 'data' => $notificationToUpdate];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }

    public function viewed($id){
        try{
            $notification = Notification::find($id);

            if (! isset($notification)) {
                throw new Exception('Notificação não encontrada', 400);
            }

            $notificationDescription = $notification->description;
            $notification->viewed = true;
            $notification->update();

            Log::create([
                'description' => 'Visualizou uma notificação',
                'user_id' => Auth::user()->id,
                'request' => json_encode(['name' => $notificationDescription]),
            ]);

        } catch (Exception $error) {

        }
    }

    public function delete($status_id)
    {
        try {
            $notification = Notification::find($status_id);

            if (! isset($notification)) {
                throw new Exception('Notificação não encontrada', 400);
            }

            $notification = $notification->description;
            $notification->delete();

            Log::create([
                'description' => 'Deletou uma notificação',
                'user_id' => Auth::user()->id,
                'request' => json_encode(['name' => $notification]),
            ]);

            return ['status' => true, 'data' => ['description' => $notification]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
