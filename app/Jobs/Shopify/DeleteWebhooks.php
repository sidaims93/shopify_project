<?php

namespace App\Jobs\Shopify;

use App\Models\Store;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteWebhooks implements ShouldQueue {

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
    public function handle()
    {
        $store = Store::where('table_id', $this->store_id)->first();
        $endpoint = getShopifyURLForStore('webhooks.json', $store);
        $headers = getShopifyHeadersForStore($store);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
        $webhooks = $response['body']['webhooks'];
        foreach($webhooks as $webhook) {
            $endpoint = getShopifyURLForStore('webhooks/'.$webhook['id'].'.json', $store);
            $headers = getShopifyHeadersForStore($store);
            $response = $this->makeAnAPICallToShopify('DELETE', $endpoint, null, $headers);
            Log::info('Response for deleting webhooks');
            Log::info($response);
        }
    }
}
