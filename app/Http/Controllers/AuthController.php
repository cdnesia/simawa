<?php

namespace App\Http\Controllers;

use App\Models\MasterMahasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $remember = $request->boolean('remember');

        $identifier = $credentials['email'];
        $plainPassword = $credentials['password'];

        $user = User::where('email', $identifier)
            ->orWhere('npm', $identifier)
            ->first();

        if ($user) {
            if (Hash::check($plainPassword, $user->password)) {
                Auth::login($user, $remember);
                $request->session()->regenerate();
                return redirect()->intended('/home');
            } else {
                return back()->withErrors([
                    'email' => 'NPM/Email/password salah.',
                ]);
            }
        }

        $mhs = MasterMahasiswa::where('npm', $identifier)
            ->where('npm', $plainPassword)
            ->first();

        if (!$mhs) {
            return back()->withErrors([
                'email' => 'Akun tidak ditemukan.',
            ])->onlyInput('email');
        }

        $email = $mhs->email;
        if (blank($email)) {
            $localPart = Str::slug($mhs->nama_mahasiswa ?: ($mhs->npm ?? 'user'), '.');
            $email = $localPart . '@local.test';

            if (User::where('email', $email)->exists()) {
                $email = $localPart . '+' . ($mhs->npm ?? Str::random(4)) . '@local';
            }
        }

        $user = User::create([
            'npm' => $mhs->npm,
            'name' => $mhs->nama_mahasiswa,
            'email' => $email,
            'password' => Hash::make($mhs->npm ?? 'password'),
        ]);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->url('/beranda');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
