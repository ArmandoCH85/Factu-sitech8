<?php

namespace App\Http\Controllers\AdminReseller;

use App\Http\Controllers\Controller;
use App\Models\System\User;
use App\Support\ResellerSystemAdminModules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        $users = User::where('reseller_id', auth()->user()->id)->orderByDesc('id')->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $allowed = ResellerSystemAdminModules::allowedKeys();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:system.users,email'],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['nullable', 'boolean'],
            'module_permissions' => ['required', 'array'],
            'module_permissions.*' => ['string', Rule::in($allowed)],
        ]);

        $token = $this->generateUniqueApiToken();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'api_token' => $token,
            'reseller_id' => auth()->user()->id,
            'status' => $data['status'] ?? true,
            'module_permissions' => ResellerSystemAdminModules::normalizePermissions($data['module_permissions']),
        ]);

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

        if (!$request->filled('password')) {
            $request->merge(['password' => null]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('system.users', 'email')->ignore($administrator->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['required', 'boolean'],
            'module_permissions' => ['required', 'array'],
            'module_permissions.*' => ['string', Rule::in($allowed)],
        ]);

        $administrator->name = $data['name'];
        $administrator->email = $data['email'];
        $administrator->status = $data['status'];
        $administrator->module_permissions = ResellerSystemAdminModules::normalizePermissions($data['module_permissions']);

        if (!empty($data['password'])) {
            $administrator->password = Hash::make($data['password']);
        }

        $administrator->save();

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

    protected function generateUniqueApiToken(): string
    {
        do {
            $token = Str::random(40);
        } while (User::where('api_token', $token)->exists());

        return $token;
    }
}
