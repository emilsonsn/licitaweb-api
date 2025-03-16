<?php

namespace App\Services\TenderOccurrence;

use App\Models\ClientLog;
use App\Models\Tender;
use App\Models\TenderLog;
use App\Models\TenderOccurrence;
use App\Models\TenderOccurrenceFile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TenderOccurrenceService
{
    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term;
            $tender_id = $request->tender_id;
            $user_id = $request->user_id;

            $tenderOccurrences = TenderOccurrence::with('files')->orderBy('id', 'desc');

            if (isset($search_term)) {
                $tenderOccurrences->where('title', 'LIKE', "%{$search_term}%")
                    ->orWhere('description', 'LIKE', "%{$search_term}%");
            }

            if (isset($tender_id)) {
                $tenderOccurrences->where('tender_id', $tender_id);
            }

            if (isset($user_id)) {
                $tenderOccurrences->where('user_id', $user_id);
            }

            $tenderOccurrences = $tenderOccurrences->paginate($perPage);

            return $tenderOccurrences;
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $rules = [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'tender_id' => ['required', 'integer'],
                'files' => ['nullable', 'array'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $data = $validator->validated();

            $data['user_id'] = Auth::user()->id;

            $tenderOccurrence = TenderOccurrence::create($data);

            $files = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('tenders/occurrences', 'public');

                    $files[] = TenderOccurrenceFile::create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'tender_occurrence_id' => $tenderOccurrence->id,
                    ]);
                }
            }

            $tenderOccurrence['files'] = $files;

            $tender = Tender::find($request->tender_id);

            if (! isset($tender)) {
                throw new Exception('Licitação não encontrado');
            }

            if(isset($tender->client_id)){
                ClientLog::create([
                    'description' => 'Ocorrencia do edital vinculado ao cliente criado',
                    'user_id' => Auth::user()->id,
                    'client_id' => $tender->client_id,
                    'request' => json_encode($request->all())
                ]);
            }

            if($tender->status == 3){
                TenderLog::create([
                    'description' => 'Ocorrencia do edital errematado criada',
                    'user_id' => Auth::user()->id,
                    'tender_id' => $tender->id,
                    'request' => json_encode($request->all())
                ]);
            }

            return ['status' => true, 'data' => $tenderOccurrence];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
