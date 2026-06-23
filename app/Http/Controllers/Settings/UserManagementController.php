<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->orderBy('name');

        if ($request->filled('role')) $query->role($request->role);
        if ($request->filled('status')) $query->where('status', $request->status === 'active');

        $users = $query->paginate(25);
        $roles = Role::all();

        return response()->json([
            'success' => true,
            'data' => $users->map(fn ($u) => [
                'id' => $u->id, 'name' => $u->name, 'email' => $u->email,
                'role' => $u->primaryRole(), 'status' => $u->status,
                'last_login' => $u->last_login_at?->diffForHumans() ?? 'Never',
                'is_online' => $u->last_login_at && $u->last_login_at->gt(now()->subMinutes(15)),
                'access_expires_at' => $u->access_expires_at?->format('Y-m-d H:i'),
            ]),
            'total' => $users->total(),
        ]);
    }

    public function page()
    {
        $roles = Role::all();
        return view('settings.users', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'whatsapp' => 'required|string|max:20',
            'gender' => 'required|in:male,female',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
            'access_expires_at' => 'nullable|date',
        ]);

        $tempPassword = Str::random(10);

        $user = User::create([
            'name' => $request->name, 'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'whatsapp' => $request->whatsapp, 'gender' => $request->gender,
            'access_expires_at' => $request->access_expires_at,
            'force_password_change' => true,
            'status' => true,
        ]);

        $user->assignRole($request->roles);

        AuditLog::record('CREATE', 'User Management', "New user created: {$user->name} ({$request->roles[0]})");

        return response()->json([
            'success' => true,
            'message' => "{$user->name} created successfully.",
            'temp_password' => $tempPassword,
        ]);
    }

    public function resetPassword(User $user)
    {
        $tempPassword = Str::random(10);
        $user->update(['password' => Hash::make($tempPassword), 'force_password_change' => true]);

        AuditLog::record('UPDATE', 'User Management', "Password reset for {$user->name}");

        return response()->json([
            'success' => true,
            'message' => 'Temporary password generated. Share this with the user — they must change it on next login.',
            'temp_password' => $tempPassword,
        ]);
    }

    public function toggleStatus(User $user)
    {
        $user->status = !$user->status;
        $user->save();

        if (!$user->status) {
            \DB::table('sessions')->where('user_id', $user->id)->delete(); // force logout when disabled
        }

        AuditLog::record($user->status ? 'ENABLE' : 'DISABLE', 'User Management', "{$user->name} account ".($user->status ? 'activated' : 'deactivated'));

        return response()->json([
            'success' => true,
            'message' => $user->name.' has been '.($user->status ? 'activated' : 'deactivated').'.',
            'status' => $user->status,
        ]);
    }

    public function updateRoles(Request $request, User $user)
    {
        $request->validate(['roles' => 'required|array|min:1', 'roles.*' => 'exists:roles,name']);
        $before = $user->roles->pluck('name')->toArray();
        $user->syncRoles($request->roles);

        AuditLog::record('UPDATE', 'User Management', "{$user->name} roles changed", ['roles' => $before], ['roles' => $request->roles]);

        return response()->json(['success' => true, 'message' => 'Roles updated successfully.']);
    }

    public function updateAccessExpiry(Request $request, User $user)
    {
        $request->validate(['access_expires_at' => 'nullable|date']);
        $user->update(['access_expires_at' => $request->access_expires_at]);

        AuditLog::record('UPDATE', 'User Management', "{$user->name} access expiry set to ".($request->access_expires_at ?? 'none'));

        return response()->json(['success' => true, 'message' => 'Access expiry updated.']);
    }

    public function loginHistory(User $user)
    {
        $history = AuditLog::where('user_id', $user->id)
            ->where('action', 'LOGIN')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['created_at', 'ip_address', 'user_agent']);

        return response()->json(['success' => true, 'data' => $history]);
    }

    public function destroy(User $user)
    {
        $name = $user->name;
        $user->delete();

        AuditLog::record('DELETE', 'User Management', "User deleted: {$name}");

        return response()->json(['success' => true, 'message' => "{$name} deleted successfully."]);
    }
}
