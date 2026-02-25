<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'extra_transaction_days' => (int) Setting::get('extra_transaction_days', 0),
                'app_name'               => Setting::get('app_name', 'SIMRAN UNISYA'),
            ],
            'approvers' => User::where('role', UserRole::Approver)
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'extra_transaction_days' => ['required', 'integer', 'min:0', 'max:365'],
            'app_name'               => ['required', 'string', 'max:100'],
        ]);

        Setting::set('extra_transaction_days', $validated['extra_transaction_days']);
        Setting::set('app_name', $validated['app_name']);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}