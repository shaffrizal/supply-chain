<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan halaman daftar user.
     */
  public function index()
{
    // Mengambil semua data user dari database
    $users = User::all();

    // Kirim variabel $users ke view admin.users.index
    return view('admin.users.index', compact('users'));
}

    /**
     * Memproses data ketika tombol "Save Operator" diklik.
     */
    public function store(Request $request)
    {
        // 1. Validasi input formulir agar aman dari duplikasi atau data kosong
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:Admin,Analyst,Operator,Viewer',
            'department' => 'required|string|max:100',
        ]);

        // 2. Simpan data user baru ke database
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department' => $request->department,
        ]);

        // 3. Kembalikan ke halaman daftar user dengan alert sukses
        return redirect()->route('admin.users.index')
                         ->with('success', 'User operator baru berhasil didaftarkan ke sistem.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(['Admin', 'Analyst', 'Operator', 'Viewer'])],
            'department' => ['required', 'string', 'max:100'],
        ]);
        if (blank($validated['password'] ?? null)) unset($validated['password']);
        $user->update($validated);

        return back()->with('success', 'User account successfully updated.');
    }

    public function destroy(User $user)
    {
        abort_if(auth()->id() === $user->id, 422, 'You cannot delete your own active account.');
        abort_if($user->role === 'Admin' && User::where('role', 'Admin')->count() <= 1, 422, 'The last administrator cannot be deleted.');
        $user->delete();

        return back()->with('success', 'User access successfully revoked.');
    }
}
