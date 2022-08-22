<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class InstallPrivateApp extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return Auth::check() && Auth::user()->hasPermissionTo('all-access');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'email' => [ 'required', 'email', 'unique:users,email', 'min:3', 'max:200' ],
            'password' => [ 'required', 'string', 'min:3', 'max:100' ],
            'api_key' => [ 'required', 'string' ],
            'api_secret_key' => [ 'required', 'string' ],
            'access_token' => [ 'required', 'string' ],
            'myshopify_domain' => [ 'required', 'string', 'unique:stores,myshopify_domain' ] 
        ];
    }
}
