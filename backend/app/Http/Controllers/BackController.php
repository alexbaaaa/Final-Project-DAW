<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function login(Request $request)
    {
        $showError = $request->boolean('invalid');

        return view('admin.login', compact('showError'));
    }

    public function authenticate()
    {
        return redirect()
            ->route('admin.login', ['invalid' => 1])
            ->with('status_error', 'Password and username do not match.');
    }

    public function home()
    {
        return view('admin.home');
    }
}
