<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevOpsController extends Controller {

    public function __construct() {
        //$this->middleware('guest:devops')->except('logout');
    }

    public function devOpsLogin() {
        return view('devops.auth.login');
    }

    public function checkLogin(Request $request) {
        try {
            $email = $request->email;
            $password = $request->password;
            if(auth()->guard('devops')->attempt(['email' => $email,'password' => $password])) {
                return redirect()->route('devops.home');
            }
            return back()->with('error', 'Email or Password does not match');
        } catch(Exception $e) {
            dd($e->getMessage().' '.$e->getLine());
        }
    }

    public function dashboard() {
        dd('In dashboard for user '.Auth::guard('devops')->user()->email);
    }
}
