<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Disbursement;
use App\Models\Proposal;
use App\Models\ProposalApprover;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $paginator = User::query()
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")
            )
            ->when($request->role, fn ($q, $r) => $q->where('role', $r))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Batch dependency check — single query per dependency type, not N+1.
        // WHY batch: calling per-user checks inside through() would fire 5 queries
        // per user row. Instead, collect the page's user IDs and query each
        // dependency table once.
        $pageIds = $paginator->getCollection()->pluck('id');
        $blocked = $this->batchDependencyCheck($pageIds);

        $paginator->getCollection()->transform(fn ($u) => [
            'id'            => $u->id,
            'name'          => $u->name,
            'email'         => $u->email,
            'role'          => $u->role->label(),
            'role_value'    => $u->role->value,
            'initials'      => collect(explode(' ', $u->name))->map(fn ($w) => $w[0] ?? '')->take(2)->join(''),
            'created_at'    => $u->created_at->format('d/m/Y'),
            // can_delete: false if the user has any linked records.
            // Superadmin cannot be deleted regardless of dependencies.
            'can_delete'    => $u->role->value !== 'superadmin' && ! isset($blocked[$u->id]),
            'delete_reason' => isset($blocked[$u->id]) ? $blocked[$u->id]
                : ($u->role->value === 'superadmin' ? 'Akun superadmin tidak dapat dihapus.' : null),
        ]);

        return Inertia::render('Admin/Users/Index', [
            'users'   => $paginator,
            'filters' => $request->only('search', 'role'),
            'roles'   => $this->roleOptions(),
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
        // Cannot delete own account
        abort_if($user->id === auth()->id(), 422, 'Tidak bisa menghapus akun sendiri.');

        // Cannot delete superadmin accounts
        abort_if($user->role->value === 'superadmin', 422, 'Akun superadmin tidak dapat dihapus.');

        // Check all foreign-key dependencies to prevent orphaned data
        $reason = $this->getDeleteReason($user->id);
        if ($reason) {
            return redirect()->back()
                ->with('error', "Pengguna tidak dapat dihapus: {$reason}");
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    // ── Private helpers ───────────────────────────────────────────

    /**
     * Batch check for a collection of user IDs.
     * Returns map of user_id → reason string for IDs that cannot be deleted.
     * 5 queries total for the entire page, not N per user.
     *
     * @param  Collection<int, int>  $ids
     * @return array<int, string>
     */
    private function batchDependencyCheck(Collection $ids): array
    {
        if ($ids->isEmpty()) {
            return [];
        }

        $blocked = [];

        // 1. Proposals where user is applicant
        Proposal::whereIn('applicant_id', $ids)->select('applicant_id')
            ->distinct()->pluck('applicant_id')
            ->each(fn ($id) => $blocked[$id] = 'Pengguna ini memiliki proposal yang diajukan.');

        // 2. ProposalApprover entries
        ProposalApprover::whereIn('user_id', $ids)->select('user_id')
            ->distinct()->pluck('user_id')
            ->each(fn ($id) => $blocked[$id] ??= 'Pengguna ini terdaftar sebagai approver proposal.');

        // 3. Disbursements as PIC
        Disbursement::withoutTrashed()->whereIn('pic_id', $ids)->select('pic_id')
            ->distinct()->pluck('pic_id')
            ->each(fn ($id) => $blocked[$id] ??= 'Pengguna ini merupakan PIC pada pencairan aktif.');

        // 4. Transactions as creator
        Transaction::whereIn('created_by', $ids)->select('created_by')
            ->distinct()->pluck('created_by')
            ->each(fn ($id) => $blocked[$id] ??= 'Pengguna ini memiliki transaksi tercatat.');

        return $blocked;
    }

    /**
     * Single-user dependency reason — used in destroy().
     */
    private function getDeleteReason(int $userId): ?string
    {
        if (Proposal::where('applicant_id', $userId)->exists()) {
            return 'pengguna ini memiliki proposal yang diajukan';
        }
        if (ProposalApprover::where('user_id', $userId)->exists()) {
            return 'pengguna ini terdaftar sebagai approver proposal';
        }
        if (Disbursement::withoutTrashed()->where('pic_id', $userId)->exists()) {
            return 'pengguna ini merupakan PIC pada pencairan aktif';
        }
        if (Transaction::where('created_by', $userId)->exists()) {
            return 'pengguna ini memiliki transaksi tercatat';
        }
        return null;
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