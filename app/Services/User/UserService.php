<?php

namespace App\Services\User;

use App\Mail\PasswordRecoveryMail;
use App\Mail\WelcomeMail;
use App\Models\PasswordRecovery;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserService
{
    public function all()
    {
        try {
            $users = User::get();

            return ['status' => true, 'data' => $users];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function search($request)
    {
        try {
            $perPage = $request->input('take', 10);
            $search_term = $request->search_term;

            $users = User::query();

            if (isset($search_term)) {
                $users->where('name', 'LIKE', "%{$search_term}%")
                    ->orWhere('email', 'LIKE', "%{$search_term}%");
            }

            $users = $users->with(['logs' => function ($query) {
                $query->where('created_at', '>=', now()->subMinutes(5));
            }])->paginate($perPage);

            $users->getCollection()->transform(function ($user) {
                $user['is_online'] = $user->logs->isNotEmpty();
                unset($user->logs);

                return $user;
            });

            return $users;
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function getUser()
    {
        try {
            $user = auth()->user();

            if ($user) {
                // Cast para o tipo correto
                $user = $user instanceof User ? $user : User::find($user->id);

                return ['status' => true, 'data' => $user];
            }

            return ['status' => false, 'error' => 'Usuário não autenticado', 'statusCode' => 401];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function cards()
    {
        try {
            $users = User::selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
                ->first();

            $users->inactive = $users->total - $users->active;

            return [
                'status' => true,
                'data' => [
                    'total' => $users->total,
                    'active' => $users->active,
                    'inactive' => $users->inactive,
                ],
            ];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function create($request)
    {
        try {
            $request['photo'] = $request['photo'] == 'null' ? null : $request['photo'];

            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'nullable|string|min:8',
                'phone' => 'nullable|string',
                'cpf_cnpj' => 'nullable|string',
                'birth_date' => 'nullable|date',
                'company_position_id' => 'nullable|integer',
                'sector_id' => 'nullable|integer',
                'is_active' => 'nullable|boolean|default:true',
                'role' => 'required|string|in:Admin,Manager,Collaborator',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];

            $password = str_shuffle(Str::upper(Str::random(1)).rand(0, 9).Str::random(1, '?!@#$%^&*').Str::random(5));

            $requestData = $request->all();
            $requestData['password'] = Hash::make($password);

            $validator = Validator::make($requestData, $rules);

            if ($validator->fails()) {
                return ['status' => false, 'error' => $validator->errors(), 'statusCode' => 400];
            }

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('photos', 'public');
                $fullPath = asset('storage/'.$path);
                $requestData['photo'] = $fullPath;
            }

            $user = User::create($requestData);

            Mail::to($user->email)->send(new WelcomeMail($user->name, $user->email, $password));

            return ['status' => true, 'data' => $user];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function update($request, $user_id)
    {
        try {
            $request['photo'] = $request['photo'] == 'null' ? null : $request['photo'];

            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'phone' => 'nullable|string',
                'cpf_cnpj' => 'nullable|string',
                'birth_date' => 'nullable|date',
                'company_position_id' => 'nullable|integer',
                'sector_id' => 'nullable|integer',
                'is_active' => 'nullable|boolean|default:true',
                'role' => 'required|string|in:Admin,Manager,Collaborator',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new Exception($validator->errors());
            }

            $userToUpdate = User::find($user_id);

            if (! isset($userToUpdate)) {
                throw new Exception('Usuário não encontrado');
            }

            $requestData = $validator->validated();

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('photos', 'public');
                $fullPath = asset('storage/'.$path);
                $requestData['photo'] = $fullPath;
            }

            $userToUpdate->update($requestData);

            return ['status' => true, 'data' => $userToUpdate];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function userBlock($user_id)
    {
        try {
            $user = User::find($user_id);

            if (! $user) {
                throw new Exception('Usuário não encontrado');
            }

            $user->is_active = ! $user->is_active;
            $user->save();

            return ['status' => true, 'data' => $user];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function delete($user_id)
    {
        try {
            $user = User::find($user_id);

            if (! $user) {
                throw new Exception('Usuário não encontrado');
            }

            $name = $user->name;
            $user->delete();

            return ['status' => true, 'data' => ['name' => $name]];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function requestRecoverPassword($request)
    {
        try {
            $email = $request->email;
            $user = User::where('email', $email)->first();

            if (! isset($user)) {
                throw new Exception('Usuário não encontrado.');
            }

            $code = bin2hex(random_bytes(10));

            $recovery = PasswordRecovery::create([
                'code' => $code,
                'user_id' => $user->id,
            ]);

            if (! $recovery) {
                throw new Exception('Erro ao tentar recuperar senha');
            }

            Mail::to($email)->send(new PasswordRecoveryMail($code));

            return ['status' => true, 'data' => $user];

        } catch (Exception $error) {
            Log::error('Erro na recuperação de senha: '.$error->getMessage());

            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }

    public function updatePassword($request)
    {
        try {
            $code = $request->code;
            $password = $request->password;

            $recovery = PasswordRecovery::orderBy('id', 'desc')->where('code', $code)->first();

            if (! $recovery) {
                throw new Exception('Código enviado não é válido.');
            }

            $user = User::find($recovery->user_id);
            $user->password = Hash::make($password);
            $user->save();
            $recovery->delete();

            return ['status' => true, 'data' => $user];
        } catch (Exception $error) {
            return ['status' => false, 'error' => $error->getMessage(), 'statusCode' => 400];
        }
    }
}
