<?php

namespace App\Http\Controllers\AdminReseller;

use App\Http\Controllers\Controller;
use App\Models\System\Client;
use App\Models\System\User;
use App\Support\ResellerSystemAdminModules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Página Vue del módulo (vista Blade).
     */
    public function index()
    {
        return view('system.admin_reseller.administrators.index');
    }

    /**
     * Listado JSON para la tabla (AJAX).
     */
    public function records()
    {
        $resellerId = (int) auth()->user()->id;

        $users = User::where('reseller_id', $resellerId)->orderByDesc('id')->get();

        $assignableClients = Client::withoutGlobalScopes()
            ->orderBy('name')
            ->get(['id', 'number', 'name']);

        $pivotRows = DB::table('reseller_admin_clients')
            ->whereIn('admin_user_id', $users->pluck('id'))
            ->get();

        $clientIdsByAdmin = [];
        foreach ($pivotRows as $row) {
            $aid = (int) $row->admin_user_id;
            if (!isset($clientIdsByAdmin[$aid])) {
                $clientIdsByAdmin[$aid] = [];
            }
            $clientIdsByAdmin[$aid][] = (int) $row->client_id;
        }

        foreach ($users as $user) {
            $user->setAttribute('assigned_client_ids', $clientIdsByAdmin[(int) $user->id] ?? []);
        }

        return response()->json([
            'success' => true,
            'data' => $users,
            'module_definitions' => ResellerSystemAdminModules::DEFINITIONS,
            'assignable_clients' => $assignableClients,
        ]);
    }

    public function store(Request $request)
    {
        $allowed = ResellerSystemAdminModules::allowedKeys();
        $resellerId = (int) auth()->user()->id;

        $data = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:system.users,email'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
                'status' => ['nullable', 'boolean'],
                'can_create_clients' => ['boolean'],
                'module_permissions' => ['present', 'array'],
                'module_permissions.*' => ['string', Rule::in($allowed)],
                'client_ids' => ['present', 'array'],
                'client_ids.*' => ['integer'],
            ],
            [
                'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
            ]
        );

        $this->assertClientIdsExistInSystem($data['client_ids']);

        $token = $this->generateUniqueApiToken();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'api_token' => $token,
            'reseller_id' => $resellerId,
            'status' => $data['status'] ?? true,
            'can_create_clients' => $data['can_create_clients'] ?? false,
            'module_permissions' => ResellerSystemAdminModules::normalizePermissions($data['module_permissions']),
        ]);

        $user->assignedClients()->sync($data['client_ids']);

        $user->setAttribute('assigned_client_ids', $data['client_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Administrador creado correctamente.',
            'data' => $user,
        ]);
    }

    public function update(Request $request, User $administrator)
    {
        if ((int) $administrator->reseller_id !== (int) auth()->user()->id) {
            abort(403);
        }

        $allowed = ResellerSystemAdminModules::allowedKeys();
        $resellerId = (int) auth()->user()->id;

        if (!$request->filled('password')) {
            $request->merge(['password' => null]);
        }

        $data = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('system.users', 'email')->ignore($administrator->id)],
                'password' => ['nullable', 'string', 'min:6', 'confirmed'],
                'status' => ['required', 'boolean'],
                'can_create_clients' => ['boolean'],
                'module_permissions' => ['present', 'array'],
                'module_permissions.*' => ['string', Rule::in($allowed)],
                'client_ids' => ['present', 'array'],
                'client_ids.*' => ['integer'],
            ],
            [
                'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
            ]
        );

        $this->assertClientIdsExistInSystem($data['client_ids']);

        $administrator->name = $data['name'];
        $administrator->email = $data['email'];
        $administrator->status = $data['status'];
        $administrator->can_create_clients = $data['can_create_clients'] ?? false;
        $administrator->module_permissions = ResellerSystemAdminModules::normalizePermissions($data['module_permissions']);

        if (!empty($data['password'])) {
            $administrator->password = Hash::make($data['password']);
        }

        $administrator->save();

        $administrator->assignedClients()->sync($data['client_ids']);
        $administrator->setAttribute('assigned_client_ids', $data['client_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Administrador actualizado correctamente.',
            'data' => $administrator,
        ]);
    }

    public function destroy(User $administrator)
    {
        if ((int) $administrator->reseller_id !== (int) auth()->user()->id) {
            abort(403);
        }

        $administrator->delete();

        return response()->json([
            'success' => true,
            'message' => 'Administrador eliminado correctamente.',
        ]);
    }

    /**
     * Valida que los IDs existan en la tabla clients (todas las empresas del sistema son asignables).
     */
    protected function assertClientIdsExistInSystem(array $clientIds): void
    {
        if ($clientIds === []) {
            return;
        }

        $unique = array_values(array_unique(array_map('intval', $clientIds)));
        $count = Client::withoutGlobalScopes()->whereIn('id', $unique)->count();
        if ($count !== count($unique)) {
            throw ValidationException::withMessages([
                'client_ids' => ['Uno o más clientes no existen en el sistema.'],
            ]);
        }
    }

    protected function generateUniqueApiToken(): string
    {
        do {
            $token = Str::random(40);
        } while (User::where('api_token', $token)->exists());

        return $token;
    }
}
