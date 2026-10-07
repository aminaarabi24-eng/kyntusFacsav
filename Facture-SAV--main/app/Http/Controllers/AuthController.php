<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('is_authenticated')) {
            return redirect()->route('home');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $validUsername = 'SAV';
        $validPassword = '250920';

        if ($request->username === $validUsername && $request->password === $validPassword) {
            session(['is_authenticated' => true, 'username' => $request->username]);
            return redirect()->route('home');
        }

        return back()->with('error', "Nom d'utilisateur ou mot de passe incorrect !");
    }

    public function logout()
    {
        session()->forget(['is_authenticated', 'username']);
        return redirect()->route('login');
    }
}