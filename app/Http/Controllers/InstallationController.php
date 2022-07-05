<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class InstallationController extends Controller {
    use FunctionTrait, RequestTrait;

    /**
     * Three scenarios can happen
     * New installation
     * Re-installation
     * Opening the app
     */
    
    public function startInstallation(Request $request) {
        try {
            $validRequest = $this->validateRequestFromShopify($request->all());
            if($validRequest) { 
                $shop = $request->has('shop'); //Check if shop parameter exists on the request.
                if($shop) {
                    $storeDetails = $this->getStoreByDomain($request->shop);
                    if($storeDetails !== null && $storeDetails !== false) {
                        //store record exists and now determine whether the access token is valid or not
                        //if not then forward them to the re-installation flow
                        //if yes then redirect them to the login page.

                        $validAccessToken = $this->checkIfAccessTokenIsValid($storeDetails);
                        if($validAccessToken) {
                            //Token is valid for Shopify API calls so redirect them to the login page.
                            print_r('Token is valid in the database so redirect the user to the login page');exit;
                        } else {
                            //Token is not valid so redirect the user to the re-installation phase.
                            Log::info('Re-installation for shop '.$request->shop.' Scopes '.config('custom.api_scopes') );
                            $endpoint = 'https://'.$request->shop.
                            '/admin/oauth/authorize?client_id='.config('custom.shopify_api_key').
                            '&scope='.config('custom.api_scopes').
                            '&redirect_uri='.config('app.ngrok_url').'shopify/auth/redirect';
                            return Redirect::to($endpoint);
                        }
                    } else {
                        //new installation flow should be carried out.
                        //https://{shop}.myshopify.com/admin/oauth/authorize?client_id={api_key}&scope={scopes}&redirect_uri={redirect_uri}&state={nonce}&grant_options[]={access_mode}
                        Log::info('New installation for shop '.$request->shop.' Scopes '.config('custom.api_scopes') );
                        $endpoint = 'https://'.$request->shop.
                        '/admin/oauth/authorize?client_id='.config('custom.shopify_api_key').
                        '&scope='.config('custom.api_scopes').
                        '&redirect_uri='.config('app.ngrok_url').'shopify/auth/redirect';
                        return Redirect::to($endpoint);
                    }
                } else throw new Exception('Shop parameter not present in the request');
            } else throw new Exception('Request is not valid!');
        } catch(Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
            dd($e->getMessage().' '.$e->getLine());
        }
    }

    public function handleRedirect(Request $request) {
        try {
            $validRequest = $this->validateRequestFromShopify($request->all());
            if($validRequest) {
                Log::info(json_encode($request->all()));
                if($request->has('shop') && $request->has('code')) {
                    $shop = $request->shop;
                    $code = $request->code;
                    $accessToken = $this->requestAccessTokenFromShopifyForThisStore($shop, $code);
                    if($accessToken !== false && $accessToken !== null) {
                        $shopDetails = $this->getShopDetailsFromShopify($shop, $accessToken);
                        $saveDetails = $this->saveStoreDetailsToDatabase($shopDetails, $accessToken);
                        if($saveDetails) {  
                            //At this point the installation process is complete.
                            return Redirect::to(config('app.ngrok_url').'shopify/auth/complete');
                        } else {
                            Log::info('Problem during saving shop details into the db');
                            Log::info($saveDetails);
                            dd('Problem during installation. please check logs.');
                        }
                    } else throw new Exception('Invalid Access Token '.$accessToken);
                } else throw new Exception('Code / Shop param not present in the URL');
            } else throw new Exception('Request is not valid!');
        } catch(Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
            dd($e->getMessage().' '.$e->getLine());
        }
    }

    public function saveStoreDetailsToDatabase($shopDetails, $accessToken) {
        try {
            $payload = [
                'access_token' => $accessToken,
                'myshopify_domain' => $shopDetails['myshopify_domain'],
                'id' => $shopDetails['id'],
                'name' => $shopDetails['name'],
                'phone' => $shopDetails['phone'],
                'address1' => $shopDetails['address1'],
                'address2' => $shopDetails['address2'],
                'zip' => $shopDetails['zip']
            ];
            Store::updateOrCreate(['myshopify_domain' => $shopDetails['myshopify_domain']], $payload); 
            return true;
        } catch(Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
            return false;
        }
    }

    public function completeInstallation(Request $request) {
        //At this point the installation is complete so redirect the browser to either the login page or anywhere u want.
        print_r('Installation complete !!');exit;
    }

    private function getShopDetailsFromShopify($shop, $accessToken) {
        try {
            $endpoint = getShopifyURLForStore('shop.json', ['myshopify_domain' => $shop]);
            $headers = getShopifyHeadersForStore(['access_token' => $accessToken]);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
            if($response['statusCode'] == 200) {
                $body = $response['body'];
                if(!is_array($body)) $body = json_decode($body, true);
                return $body['shop'] ?? null;
            } else {
                Log::info('Response recieved for shop details');
                Log::info($response);
                return null;
            }
        } catch(Exception $e) {
            Log::info('Problem getting the shop details from shopify');
            Log::info($e->getMessage().' '.$e->getLine());
            return null;
        }
    }

    private function requestAccessTokenFromShopifyForThisStore($shop, $code) {
        try {
            $endpoint = 'https://'.$shop.'/admin/oauth/access_token';
            $headers = ['Content-Type: application/json'];
            $requestBody = json_encode([
                'client_id' => config('custom.shopify_api_key'),
                'client_secret' => config('custom.shopify_api_secret'),
                'code' => $code
            ]);
            $response = $this->makeAPOSTCallToShopify($requestBody, $endpoint, $headers);
            if($response['statusCode'] == 200) {
                $body = $response['body'];
                if(!is_array($body)) $body = json_decode($body, true);
                Log::info('Body here');
                Log::info($body);
                if(is_array($body) && isset($body['access_token']) && $body['access_token'] !== null)
                    return $body['access_token'];
            }
            return false;
        } catch(Exception $e) {
            return false;
        }
    }

    private function validateRequestFromShopify($request) {
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

    /**
       * Write some code here that will use the Guzzle library to fetch the shop object from Shopify API
       * If it succeeds with 200 status then that means its valid and we can return true;        
     */

    private function checkIfAccessTokenIsValid($storeDetails) {
        try {
            if($storeDetails !== null && isset($storeDetails->access_token) && strlen($storeDetails->access_token) > 0) {
                $token = $storeDetails->access_token;
                $endpoint = getShopifyURLForStore('shop.json', $storeDetails);
                $headers = getShopifyHeadersForStore($storeDetails);
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers, null);
                Log::info('Response for checking the validity of token');
                Log::info($response);
                return $response['statusCode'] === 200;
            }
            return false;
        } catch(Exception $e) {
            return false;
        }
    }
}
