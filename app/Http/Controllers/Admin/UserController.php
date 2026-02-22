<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")
            )
            ->when($request->role, fn ($q, $r) => $q->where('role', $r))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->role->label(),
                'role_value' => $u->role->value,
                'initials'   => collect(explode(' ', $u->name))->map(fn ($w) => $w[0] ?? '')->take(2)->join(''),
                'created_at' => $u->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'filters' => $request->only('search', 'role'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Form', [
            'editUser' => null,
            'roles'    => $this->roleOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Form', [
            'editUser' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role->value,
            ],
            'roles' => $this->roleOptions(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'role'  => $validated['role'],
            ...($validated['password'] ? ['password' => Hash::make($validated['password'])] : []),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 422, 'Tidak bisa menghapus akun sendiri.');

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    private function roleOptions(): array
    {
        $meta = [
            'superadmin' => ['abbr' => 'SA',  'desc' => 'Akses penuh ke seluruh sistem'],
            'pic'        => ['abbr' => 'PIC', 'desc' => 'Input & kelola transaksi pencairan'],
            'auditor'    => ['abbr' => 'AUD', 'desc' => 'Baca & audit seluruh data keuangan'],
            'applicant'  => ['abbr' => 'APL', 'desc' => 'Mengajukan proposal anggaran'],
            'approver'   => ['abbr' => 'APR', 'desc' => 'Menyetujui proposal yang diteruskan'],
        ];

        return collect(UserRole::cases())->map(fn ($r) => [
            'value' => $r->value,
            'label' => $r->label(),
            'abbr'  => $meta[$r->value]['abbr'] ?? strtoupper(substr($r->value, 0, 3)),
            'desc'  => $meta[$r->value]['desc'] ?? '',
        ])->values()->all();
    }
}