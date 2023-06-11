<?php

namespace App\Http\Controllers;

use App\Jobs\Shopify\ConfigureWebhooks;
use App\Jobs\Shopify\DeleteWebhooks;
use App\Jobs\Shopify\GetWebhooks;
use App\Models\Store;
use App\Traits\FunctionTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhooksController extends Controller
{
    use FunctionTrait;
    public function configureWebhooks($id) {
        try {
            ConfigureWebhooks::dispatchNow($id);
            print_r('Done');
        } catch(Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
        }
    }

    public function deleteWebhooks($id) {
        try {
            DeleteWebhooks::dispatch($id);
            print_r('Done');
        } catch(Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
        }
    }

    public function orderCreated(Request $request) {
        Log::info('Recieved webhook for event order created');
        Log::info($request->all());
        return response()->json(['status' => true], 200);
    }

    public function orderUpdated(Request $request) {
        Log::info('Recieved webhook for event order updated');
        Log::info($request->all());
        return response()->json(['status' => true], 200);
    }

    public function productCreated(Request $request) {
        Log::info('Recieved webhook for event product created');
        Log::info($request->all());
        return response()->json(['status' => true], 200);
    }

    public function appUninstalled(Request $request) {
        Log::info('Recieved webhook for event app removed');
        Log::info($request->all());
        return response()->json(['status' => true], 200);
    }

    public function shopUpdated(Request $request) {
        Log::info('Recieved webhook for event shop updated');
        Log::info($request->all());
        return response()->json(['status' => true], 200);
    }

    public function returnCustomerData(Request $request) {
        try {
            $validRequest = $this->validateRequestFromShopify($request->all());
            $response = $validRequest ? 
                ['status' => true, 'message' => 'Not Found', 'code' => 200] : 
                ['status' => false, 'message' => 'Unauthorised!', 'code' => 401];
        } catch(Exception $e) {
            $response = ['status' => false, 'message' => 'Unauthorised!', 'code' => 401];
        }
        return response()->json($response, $response['code']);
    }

    public function deleteCustomerData(Request $request) {
        try {
            $validRequest = $this->validateRequestFromShopify($request->all());
            $response = $validRequest ? 
                ['status' => true, 'message' => 'Not Found', 'code' => 200] : 
                ['status' => false, 'message' => 'Unauthorised!', 'code' => 401];
        } catch(Exception $e) {
            $response = ['status' => false, 'message' => 'Unauthorised!', 'code' => 401];
        }
        return response()->json($response, $response['code']);
    }

    public function deleteShopData(Request $request) {
        try {
            $validRequest = $this->validateRequestFromShopify($request->all());
            $response = $validRequest ? 
                ['status' => true, 'message' => 'Success', 'code' => 200] : 
                ['status' => false, 'message' => 'Unauthorised!', 'code' => 401];
        } catch(Exception $e) {
            $response = ['status' => false, 'message' => 'Unauthorised!', 'code' => 401];
        }
        return response()->json($response, $response['code']);
    }
}
