<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\ActivityLogger;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()
            ->paginate(10);

        return view(
            'users.index',
            compact('users')
        );
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' =>
                'required|string|max:255',

            'email' =>
                'required|email|max:255|unique:users,email',

            'password' =>
                'required|string|min:8|confirmed',

            'role' =>
                'required|in:admin,staff',
        ]);

        $user = User::create([
            'name' =>
                $validated['name'],

            'email' =>
                $validated['email'],

            'password' =>
                Hash::make(
                    $validated['password']
                ),

            'role' =>
                $validated['role'],
        ]);
        ActivityLogger::log('Created', 'Users', 'Created user: '.$user->email.' ('.$user->role.')');

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User created successfully.'
            );
    }

    public function edit(User $user)
    {
        return view(
            'users.edit',
            compact('user')
        );
    }

    public function update(
        Request $request,
        User $user
    ) {
        $validated = $request->validate([
            'name' =>
                'required|string|max:255',

            'email' =>
                'required|email|max:255|unique:users,email,' .
                $user->id,

            'role' =>
                'required|in:admin,staff',

            'password' =>
                'nullable|string|min:8|confirmed',
        ]);

        $emailChanged = $user->email !== $validated['email'];
        $data = [
            'name' =>
                $validated['name'],

            'email' =>
                $validated['email'],

            'role' =>
                $validated['role'],
        ];

        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }

        if ($user->role === 'admin' && $validated['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'You cannot remove the last administrator.');
        }

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make(
                $validated['password']
            );
        }

        $user->update($data);
        ActivityLogger::log('Updated', 'Users', 'Updated user: '.$user->email.' ('.$user->role.')');

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User updated successfully.'
            );
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'You cannot delete your own account.'
                );
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'You cannot delete the last administrator.');
        }

        $email = $user->email;
        $user->delete();
        ActivityLogger::log('Deleted', 'Users', 'Deleted user: '.$email);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User deleted successfully.'
            );
    }
}
