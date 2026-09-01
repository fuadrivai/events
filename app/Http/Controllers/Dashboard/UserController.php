<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('branch')->orderByDesc('id')->paginate(20);
        return view('dashboard.users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();

        return view('dashboard.users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'branch_id' => ['nullable','exists:branches,id'],
            'role' => ['required', 'in:admin,user'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'branch_id' => $data['branch_id'] ?? null,
            'password' => Hash::make('mutiaraharapan'),
            'role' => $data['role'],
        ]);

        return redirect()->route('dashboard.users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $branches = Branch::orderBy('name')->get();

        return view('dashboard.users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'role' => ['required', 'in:admin,user'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->branch_id = $data['branch_id'] ?? null;
        $user->role = $data['role'];

        $user->save();

        return redirect()->route('dashboard.users.index')->with('success', 'User berhasil diperbarui.');
    }
}
