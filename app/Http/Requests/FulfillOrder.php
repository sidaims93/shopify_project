<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FulfillOrder extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $check = Auth::check(); //Check if logged in
        if($check)
            return Auth::user()->can('write-orders');
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required',
            'no_of_packages' => 'required',
            'tracking_url' => 'required',
            'shipping_company' => 'required',
            'number' => 'required',
            'lineItemId' => 'required',
            'order_id' => 'required',
            'number' => 'required',
            'notify_customer' => 'nullable',
        ];
    }
}
