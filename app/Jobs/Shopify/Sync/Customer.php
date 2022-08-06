<?php

namespace App\Jobs\Shopify\Sync;

use App\Models\Customer as ModelsCustomer;
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

class Customer implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use FunctionTrait, RequestTrait;
    public $store;
    public $user;
    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct($user, $store) {
        $this->store = $store;
        $this->user = $user;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle() {
        try {
            //GraphQL implementation of syncing customers
            $headers = getGraphQLHeadersForStore($this->store);
            $endpoint = getShopifyURLForStore('graphql.json', $this->store);
            $cursor = null;
            do {
                $query = $this->getQueryObjectForCustomers($cursor);
                $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $query);
                if($response['statusCode'] === 200) 
                    $this->saveCustomerResponseInDB($this->user, $this->store, $response['body']['data']['customers']['edges']);
                $cursor = $this->getCursorFromResponse($response['body']['data']['customers']['pageInfo']);
            } while($cursor !== null);
        } catch(Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
        }
    }

    public function getQueryObjectForCustomers($cursor) {
        try {
            $query = '{';
            $filter = '(first : 5'. ($cursor !== null ? ', after : "'.$cursor.'"' : null).')';
            $query .= '  customers'.$filter.' { 
                                edges { 
                                    node { 
                                        id email 
                                        createdAt updatedAt firstName lastName numberOfOrders phone 
                                        defaultAddress { address1 address2 city country id firstName lastName phone province zip }
                                    } 
                                } 
                                pageInfo { 
                                    hasNextPage endCursor hasPreviousPage startCursor 
                                } 
                            }';
            $query .= '}';
            return ['query' => $query];
        } catch(Exception $e) {
            return null;
        }
    }

    public function saveCustomerResponseInDB($user, $store, $response) {
        try {
            foreach($response as $edges) {
                $node = $edges['node'];
                $id = (int) str_replace('gid://shopify/Customer/', '', $node['id']);
                $payload = [
                    'id' => $id,
                    'store_id' => $store->table_id,
                    'email' => $node['email'],
                    'created_at' => $node['createdAt'],
                    'updated_at' => $node['updatedAt'],
                    'first_name' => $node['firstName'],
                    'last_name' => $node['lastName'],
                    'orders_count' => (int) $node['numberOfOrders'],
                    'phone' => $node['phone'],
                    'admin_graphql_api_id' => $node['id'],
                    'default_address' => json_encode($node['defaultAddress'])
                ];
                $update_payload = [
                    'store_id' => $store->table_id,
                    'id' => $id
                ];
                ModelsCustomer::updateOrCreate($update_payload, $payload);
            }
            return true;
        } catch(Exception $e) {
            Log::info($e->getMessage());
            return true;
        }
    }

    public function getCursorFromResponse($pageInfo) {
        try {
            return $pageInfo['hasNextPage'] === true ? $pageInfo['endCursor'] : null;
        } catch(Exception $e) {
            Log::info($e->getMessage());
            return null;
        }
    }
}
