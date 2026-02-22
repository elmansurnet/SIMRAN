<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile', [
            'user' => [
                'id'    => $request->user()->id,
                'name'  => $request->user()->name,
                'email' => $request->user()->email,
                'role'  => $request->user()->role->label(),
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            if (! empty($data['current_password'])) {
                if (! Hash::check($data['current_password'], $request->user()->password)) {
                    return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
                }
            }
        }

        $updateData = ['name' => $data['name'], 'email' => $data['email']];
        if (! empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        $request->user()->update($updateData);

        return redirect()->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
