<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::all();
        return view('admin.usuarios', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/admin/usuarios')->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();
        return redirect('/admin/usuarios')->with('success', 'Usuário removido com sucesso!');
    }
}
