<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateMember extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize() {
        $check = Auth::check(); //Check if logged in
        if($check)
            return Auth::user()->can('write-members');
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules() {
        return [
            'email' => [ 'required', 'email', 'unique:users,email', 'min:3', 'max:200' ],
            'name' => [ 'required', 'min:2', 'max:100' ],
            'password' => [ 'required', 'string', 'confirmed', 'min:3', 'max:100' ],
            'permissions' => [ 'array', 'required', 'min:1' ],
            'permissions.*' => [ 'required', 'string', 'distinct', Rule::in(config('custom.default_permissions')) ]
        ];
    }

    public function messages() {
        return [
            'permissions.required' => 'You need to provide at least one valid permission for the team member',
            'permissions.*.required' => 'Invalid permission'
        ];
    }
}
