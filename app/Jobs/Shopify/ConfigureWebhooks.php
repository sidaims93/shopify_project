<?php

namespace App\Jobs\Shopify;

use App\Models\Store;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConfigureWebhooks implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use FunctionTrait, RequestTrait;

    private $store_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_id) {
        $this->store_id = $store_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try {
            $store = Store::where('table_id', $this->store_id)->first();
            $endpoint = getShopifyURLForStore('webhooks.json', $store);
            $headers = getShopifyHeadersForStore($store);
            $webhooks_config = config('custom.webhook_events');
            foreach($webhooks_config as $topic => $url) {
                $body = [
                    'webhook' => [
                        'topic' => $topic,
                        'address' => config('app.url').'webhook/'.$url,
                        'format' => 'json'
                    ]
                ];
                $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $body);
                Log::info('Response for topic '.$topic);
                Log::info($response['body']);
                //You can write a logic to save this in the database table.
            }
        } catch(Exception $e) {
            //Log::info(json_encode($e->getTrace()));
            Log::info('here in configure webhooks ' . $e->getMessage().' '.$e->getLine());
        }
    }
}
