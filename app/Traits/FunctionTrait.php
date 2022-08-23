<?php 

namespace App\Traits;

use App\Models\Store;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

trait FunctionTrait {
    public function getStoreByDomain($shop) {
        return Store::where('myshopify_domain', $shop)->first();
    }

    public function createAUserLoginForTheStore($shop_body, $user_payload) {
        $user = User::updateOrCreate(['email' => $shop_body['email']], $user_payload);
        $user->markEmailAsVerified(); //To mark this user verified without requiring them to.
        $user->assignRole('Admin');
        foreach(config('custom.default_permissions') as $permission)
            $user->givePermissionTo($permission);
        return true;
    }


    public function validateRequestFromShopify($request) {
        try {
            $arr = [];
            $hmac = $request['hmac'];
            unset($request['hmac']);
            foreach($request as $key => $value){
                $key=str_replace("%","%25",$key);
                $key=str_replace("&","%26",$key);
                $key=str_replace("=","%3D",$key);
                $value=str_replace("%","%25",$value);
                $value=str_replace("&","%26",$value);
                $arr[] = $key."=".$value;
            }
            $str = implode('&', $arr);
            $ver_hmac =  hash_hmac('sha256',$str,config('custom.shopify_api_secret'), false);
            return $ver_hmac === $hmac;
        } catch(Exception $e) {
            Log::info('Problem with verify hmac from request');
            Log::info($e->getMessage().' '.$e->getLine());
            return false;
        }
    }

}