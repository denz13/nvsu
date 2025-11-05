<?php

namespace App\Http\Controllers;

use App\Http\Request\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\students;

class AuthController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginView()
    {
        return view('login.main', [
            'layout' => 'login'
        ]);
    }

    /**
     * Authenticate login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        // Try default web guard (users table)
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            if (config('app.debug') && $request->boolean('debug')) {
                dd(['guard' => 'web', 'user' => Auth::user()]);
            }
            return response()->json(['success' => true, 'redirect' => url('/')]);
        }

        // Fallback: try students guard using id_number as login field
        // UI uses 'email' input; accept id_number there
        $student = students::where('id_number', $request->email)->first();
        if ($student) {
            $pw = (string)($request->password ?? '');
            $stored = (string)($student->password ?? '');
            $isValid = Hash::check($pw, $stored) || $stored === $pw;
            if ($isValid) {
                Auth::guard('students')->login($student);
                $request->session()->regenerate();
                if (config('app.debug') && $request->boolean('debug')) {
                    dd(['guard' => 'students', 'user' => Auth::guard('students')->user()]);
                }
                return response()->json(['success' => true, 'redirect' => url('/')]);
            }
        }

        return response()->json([
            'message' => 'Wrong email or password.'
        ], 422);
    }

    /**
     * Logout user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        // Logout from both guards and invalidate session
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }
        if (Auth::guard('students')->check()) {
            Auth::guard('students')->logout();
        }
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('login');
    }
}
