<?php

namespace App\Http\Controllers;

use App\Models\LoginSecurity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class LoginSecurityController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show 2FA Setting form
     */
    public function show2faForm(Request $request){
        try {
            $user = Auth::user();
            $google2fa_url = "";
            $secret_key = "";

            if($user->loginSecurity()->exists()){
                $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
                $google2fa_url = $google2fa->getQRCodeInline(
                    'Shopify App Local Demo',
                    $user->email,
                    $user->loginSecurity->google2fa_secret
                );
                $secret_key = $user->loginSecurity->google2fa_secret;
            }

            $data = [
                'user' => $user,
                'secret' => $secret_key,
                'google2fa_url' => $google2fa_url
            ];

            return view('two_factor_auth.2fa_settings', ['data' => $data]);
        } catch(Exception $e) {
            dd($e->getMessage().' '.$e->getLine());
        }
    }

    /**
     * Generate 2FA secret key
     */
    public function generate2faSecret(Request $request){
        try {
            $user = Auth::user();
            // Initialise the 2FA class
            $google2fa = new Google2FA();

            // Add the secret key to the registration data
            $login_security = LoginSecurity::firstOrNew(array('user_id' => $user->id));
            $login_security->user_id = $user->id;
            $login_security->google2fa_enable = 0;
            $login_security->google2fa_secret = $google2fa->generateSecretKey();
            $login_security->save();

            return redirect()->route('show2FASettings')->with('success',"Secret key is generated.");
        } catch(Exception $e) {
            dd($e->getMessage().' '.$e->getLine());
        }
    }

    /**
     * Enable 2FA
     */
    public function enable2fa(Request $request){
        try {
            $user = Auth::user();
            $google2fa = new Google2FA();
            $secret = $request->input('secret');
            $valid = $google2fa->verifyKey($user->loginSecurity->google2fa_secret, $secret);

            if($valid){
                $user->loginSecurity->google2fa_enable = 1;
                $user->loginSecurity->save();
                return redirect()->route('show2FASettings')->with('success',"2FA is enabled successfully.");
            }else{
                return redirect()->route('show2FASettings')->with('error',"Invalid verification Code, Please try again.");
            }
        } catch(Exception $e) {
            dd($e->getMessage().' '.$e->getLine());
        }
    }

    /**
     * Disable 2FA
     */
    public function disable2fa(Request $request){
        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            // The passwords matches
            return back()->with("error","Your password does not matches with your account password. Please try again.");
        }
        $validatedData = $request->validate([
            'current-password' => 'required',
        ]);
        $user = Auth::user();
        $user->loginSecurity->google2fa_enable = 0;
        $user->loginSecurity->save();
        return redirect()->route('show2FASettings')->with('success',"2FA is now disabled.");
    }
}