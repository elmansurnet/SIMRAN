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
use Illuminate\Support\Facades\Auth;
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
            ->through(function ($u) {
                [$canDelete, $deleteReason] = $this->deletionCheck($u);
                return [
                    'id'            => $u->id,
                    'name'          => $u->name,
                    'email'         => $u->email,
                    'role'          => $u->role->label(),
                    'role_value'    => $u->role->value,
                    'initials'      => collect(explode(' ', $u->name))->map(fn ($w) => $w[0] ?? '')->take(2)->join(''),
                    'created_at'    => $u->created_at->format('d/m/Y'),
                    // ✅ PART C/D: expose deletion eligibility to the UI
                    'can_delete'    => $canDelete,
                    'delete_reason' => $deleteReason,
                ];
            });

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
        // ✅ PART D — Cannot delete own account
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        // ✅ PART D — Check all foreign-key dependencies before deletion
        [$canDelete, $reason] = $this->deletionCheck($user);

        if (! $canDelete) {
            return back()->with('error', "Pengguna '{$user->name}' tidak dapat dihapus: {$reason}");
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    // ── Private helpers ───────────────────────────────

    /**
     * Check whether this user can be safely deleted.
     *
     * Returns [bool $canDelete, string $reason].
     * $reason is an empty string when deletion is allowed.
     *
     * Dependencies checked:
     *   • proposals.applicant_id  — user submitted proposals
     *   • proposals.pic_id        — user is PIC of proposals
     *   • proposals.reviewed_by   — user reviewed proposals as superadmin
     *   • proposal_approvers.user_id — user is assigned approver
     *   • disbursements.pic_id    — user is PIC of disbursements
     *   • transactions.created_by — user created transactions
     */
    private function deletionCheck(User $user): array
    {
        $reasons = [];

        if (Proposal::where('applicant_id', $user->id)->exists()) {
            $count = Proposal::where('applicant_id', $user->id)->count();
            $reasons[] = "pemohon dari {$count} proposal";
        }

        if (Proposal::where('pic_id', $user->id)->exists()) {
            $count = Proposal::where('pic_id', $user->id)->count();
            $reasons[] = "PIC dari {$count} proposal";
        }

        if (ProposalApprover::where('user_id', $user->id)->exists()) {
            $count = ProposalApprover::where('user_id', $user->id)->count();
            $reasons[] = "approver dari {$count} proposal";
        }

        if (Disbursement::withTrashed()->where('pic_id', $user->id)->exists()) {
            $count = Disbursement::withTrashed()->where('pic_id', $user->id)->count();
            $reasons[] = "PIC dari {$count} pencairan";
        }

        if (Transaction::where('created_by', $user->id)->exists()) {
            $count = Transaction::where('created_by', $user->id)->count();
            $reasons[] = "pembuat {$count} transaksi";
        }

        if (empty($reasons)) {
            return [true, ''];
        }

        return [false, 'Pengguna ini tercatat sebagai ' . implode(', ', $reasons) . '.'];
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