<?php

namespace App\Http\Controllers;

use App\Jobs\Shopify\ConfigureWebhooks;
use App\Jobs\Shopify\DeleteWebhooks;
use App\Jobs\Shopify\GetWebhooks;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhooksController extends Controller
{
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
}
