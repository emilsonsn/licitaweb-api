<?php

namespace App\Services\Tender;

use App\Models\Status;
use App\Models\Tender;
use App\Models\TenderAttachment;
use App\Models\TenderItem;
use App\Models\TenderStatus;
use App\Models\TenderTask;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TenderService
{
    public function all()
    {
        try {
            $tenders = Tender::with('modality', 'user')->get();
            return ['status' => true, 'data' => $tenders];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term ?? null;
            $status = $request->status ?? null;
            $status_id = $request->status_id ?? null;

            $tenders = Tender::with('modality', 'user', 'tenderStatus', 'task');

            if (isset($search_term)) {
                $tenders->where('number', 'LIKE', "%{$search_term}%")
                        ->orWhere('organ', 'LIKE', "%{$search_term}%");
            }

            if(isset($status)){
                $tenders->where('status', $status);
            }

            if(isset($status)){
                $tenders->wherehas('tenderStatus', function($query) use($status_id){
                    $query->where('status_id', $status_id);
                });
            }

            return $tenders->paginate($perPage);
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'number' => 'nullable|string',
                'organ' => 'nullable|string',
                'modality_id' => 'required|integer|exists:modalities,id',
                'contest_date' => 'required|date',
                'object' => 'required|string',
                'estimated_value' => 'nullable|numeric',
                'status' => 'required|string',
                'items_count' => 'nullable|integer',
                'user_id' => 'required|integer|exists:users,id',
                'items' => 'required|array|min:1',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,png,xls,xlsx|max:2048',
            ];

            $data = $request->all();
    
            $validator = Validator::make($data, $rules);
    
            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }
    
            DB::beginTransaction();
    
            $tender = Tender::create($validator->validated());
                
            foreach ($request->items as $itemData) {
                $itemData = json_decode($itemData, true);
                TenderItem::create([
                    'item' => $itemData['item'],
                    'tender_id' => $tender->id,
                ]);
            }

            if ($request->attachments) {
                foreach ($request->attachments as $attachment) {
                    $path = $attachment->store('tenders/attachments', 'public');
                    $fullPath = 'storage/' . $path;
    
                    $tender->attachments()->create([
                        'filename' => $attachment->getClientOriginalName(),
                        'path' => $fullPath,
                        'user_id' => $request->user_id,
                    ]);
                }
            }
    
            $firstStatus = Status::orderBy('id', 'asc')->first();
    
            if ($firstStatus) {
                TenderStatus::create([
                    'tender_id' => $tender->id,
                    'status_id' => $firstStatus->id,
                    'position' => $firstStatus->position ?? 1,
                ]);
            }
    
            DB::commit();
    
            return ['status' => true, 'data' => $tender];
        } catch (Exception $error) {
            DB::rollBack();
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $tender_id)
    {
        try {

            $rules = [
                'number' => 'nullable|string',
                'organ' => 'nullable|string',
                'modality_id' => 'required|integer|exists:modalities,id',
                'contest_date' => 'required|date',
                'object' => 'required|string',
                'estimated_value' => 'nullable|numeric',
                'status' => 'required|string',
                'items_count' => 'nullable|integer',
                'user_id' => 'required|integer|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.item' => 'required|string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,png,xls,xlsx|max:2048',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $tender = Tender::find($tender_id);

            if (!$tender) {
                throw new Exception('Licitação não encontrada', 400);
            }

            DB::beginTransaction();

            $tender->update($validator->validated());

            if ($request->attachments) {
                foreach ($request->attachments as $attachment) {
                    $path = $attachment->store('tenders/attachments', 'public');
                    $fullPath = 'storage/' . $path;
    
                    $tender->attachments()->create([
                        'filename' => $attachment->getClientOriginalName(),
                        'path' => $fullPath,
                        'user_id' => $request->user_id,
                    ]);
                }
            }
    
            if ($request->has('attachments')) {
                foreach ($request->file('attachments') as $attachment) {
                    $path = $attachment->store('tenders/attachments', 'public');
                    $fullPath = 'storage/' . $path;
    
                    $tender->attachments()->create([
                        'filename' => $attachment->getClientOriginalName(),
                        'path' => $fullPath,
                        'user_id' => $request->user_id,
                    ]);
                }
            }

            DB::commit();

            return ['status' => true, 'data' => $tender];
        } catch (Exception $error) {
            DB::rollBack();
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => $error->getCode()];
        }
    }

    public function updateStatus($tender_id, $new_status_id, $position)
    {
        try {
            $tenderStatus = TenderStatus::where('tender_id', $tender_id)->first();
    
            if (!$tenderStatus) throw new Exception('Status da licitação não encontrado');
    
            DB::transaction(function () use ($tenderStatus, $new_status_id, $position, $tender_id) {
                if ($tenderStatus->status_id == $new_status_id) {
                    TenderStatus::where('status_id', $new_status_id)
                        ->where('position', '>=', $position)
                        ->where('tender_id', '!=', $tender_id)
                        ->increment('position');
                } else {
                    TenderStatus::where('status_id', $tenderStatus->status_id)
                        ->where('position', '>', $tenderStatus->position)
                        ->decrement('position');
    
                    TenderStatus::where('status_id', $new_status_id)
                        ->where('position', '>=', $position)
                        ->increment('position');
                }
    
                $tenderStatus->update([
                    'status_id' => $new_status_id,
                    'position' => $position,
                ]);
            });

            return ['status' => true, 'data' => $tenderStatus];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function delete($tender_id)
    {
        try {
            $tender = Tender::find($tender_id);

            if (!$tender) throw new Exception('Licitação não encontrada');

            $tenderId = $tender->id;
            $tender->delete();

            return ['status' => true, 'data' => ['tenderId' => $tenderId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function deleteAttachment($attachmentId)
    {
        try {
            $attachment = TenderAttachment::find($attachmentId);

            if (!$attachment) throw new Exception('Anexo não encontrado');

            $attachmentId = $attachment->id;
            $attachment->delete();

            return ['status' => true, 'data' => ['attachmentId' => $attachmentId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function deleteItem($itemId)
    {
        try {
            $item = TenderItem::find($itemId);

            if (!$item) throw new Exception('Item não encontrado');

            $itemId = $item->id;
            $item->delete();

            return ['status' => true, 'data' => ['itemId' => $itemId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function deleteTask($taskId)
    {
        try {
            $task = TenderTask::find($taskId);

            if (!$task) throw new Exception('Tarefa não encontrada');

            $taskId = $task->id;
            $task->delete();

            return ['status' => true, 'data' => ['taskId' => $taskId]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

}