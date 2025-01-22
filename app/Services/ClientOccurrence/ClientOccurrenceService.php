<?php

namespace App\Services\ClientOccurrence;

use App\Models\ClientOccurrence;
use App\Models\ClientOccurrenceFile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientOccurrenceService
{

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term;
            $client_id = $request->client_id;
            $user_id = $request->user_id;

            $clientOccurrence = ClientOccurrence::with('files')->orderBy('id', 'desc');

            if(isset($search_term)){
                $clientOccurrence->where('title', 'LIKE', "%{$search_term}%")
                    ->orWhere('description', 'LIKE', "%{$search_term}%");
            }

            if(isset($client_id)){
                $clientOccurrence->where('client_id', $client_id);
            }

            if(isset($user_id)){
                $clientOccurrence->where('user_id', $user_id);
            }

            $clientOccurrence = $clientOccurrence->paginate($perPage);

            return $clientOccurrence;
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
                'client_id' => ['required', 'integer'],
                'files' => ['nullable', 'array']
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors(), 400);
            }

            $data = $validator->validated();

            $data['user_id'] = Auth::user()->id;

            $clientOccurrence = ClientOccurrence::create($data);

            $files = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('tenders/occurrences', 'public');
    
                    $files[] = ClientOccurrenceFile::create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'client_occurrence_id' => $clientOccurrence->id,
                    ]);
                }
            }

            $clientOccurrence['files'] = $files;

            return ['status' => true, 'data' => $clientOccurrence];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}