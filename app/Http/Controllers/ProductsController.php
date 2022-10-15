<?php

namespace App\Http\Controllers;

use App\Jobs\Shopify\Sync\Product;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller {
    use RequestTrait, FunctionTrait;

    public function __construct() {
        $this->middleware('auth');        
    }

    public function create() {
        $locations = Auth::user()->getShopifyStore->getLocations()->select(['id', 'name'])->where('legacy', 0)->get();
        return view('products.create', ['locations' => $locations]);
    }

    public function getHTMLForAddingVariant(Request $request) {
        $locations = Auth::user()->getShopifyStore->getLocations()->select(['id', 'name'])->where('legacy', 0)->get();
        return response()->json([
            'status' => true, 
            'html' => view('products.partials.add_variant', [
                'count' => $request->count,
                'locations' => $locations
            ])->render()
        ]);
    }

    public function publishProduct(Request $request) {
        $request = $request->all();
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $locations = $store->getLocations()->where('legacy', 0)->select(['id', 'name', 'admin_graphql_api_id', 'legacy'])->get()->toArray();
        $productCreateMutation = 'productCreate (input: {'.$this->getGraphQLPayloadForProductPublish($request, $locations).'}) { 
            product { id }
            userErrors { field message }
        }';
        $mutation = 'mutation { '.$productCreateMutation.' }';
        $endpoint = getShopifyURLForStore('graphql.json', $store);
        $headers = getShopifyHeadersForStore($store);
        $payload = ['query' => $mutation];
        $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);
        Product::dispatch($user, $store);
        return back()->with('success', 'Product Created!');
    }

    private function returnTags($tags) {
        try {
            $tags = explode(',', $tags);
            $return_val = [];
            foreach($tags as $tag)
                $return_val[] = '"'.$tag.'"';
            return implode(',', $return_val);
        } catch(Exception $e) {
            return null;
        }
    }

    private function getGraphQLPayloadForProductPublish($request, $locations) {
        $temp = [];
        $temp[] = 
          ' title: "'.$request['title'].'",
            published: true,
            vendor: "'.$request['vendor'].'" ';
        if(isset($request['desc']) && $request['desc'] !== null)
            $temp[] = ' descriptionHtml: "'.$request['desc'].'"';
        if(isset($request['product_type'])) 
            $temp[] = ' productType: "'.$request['product_type'].'"';
        if(isset($request['tags'])) 
            $temp[] = ' tags: ['.$this->returnTags($request['tags']).']';
        if(isset($request['variant_title']) && is_array($request['variant_title'])) {
            $temp[] = ' options: ["'.implode(', ',$request['variant_title']).'"]';
            $temp[] = ' variants: ['.$this->getVariantsGraphQLConfig($request, $locations).']';
        }  

        return implode(',', $temp);
    }

    private function getVariantsGraphQLConfig($request, $locations) {
        try {
            //dd($request);
            if(is_array($request['variant_title'])) {
                $str = [];
                foreach($request['variant_title'] as $key => $variant_title){
                    $str[] = '{
                        taxable: false,
                        title: "'.$variant_title.'",
                        compareAtPrice: '.$request['variant_caprice'][$key].',
                        sku: "'.$request['sku'][$key].'",
                        options: [ "'.$variant_title.'" ],
                        inventoryItem: {cost: '.$request['variant_price'][$key].', tracked: true},
                        inventoryQuantities: '.$this->getInventoryQuantitiesString($key, $request, $locations).',
                        inventoryManagement: SHOPIFY,
                        inventoryPolicy: DENY,                
                        price: '.$request['variant_price'][$key].' }';
                    }
                }
                return implode(',', $str); 
        } catch(Exception $e) {
            dd($e->getMessage().' '.$e->getLine());
            return null;
        }
    }

    public function getInventoryQuantitiesString($key, $request, $locations) {
        $str = '[';
        $temp_payload = [];
        foreach($locations as $location){
            if(isset($request[$location['id'].'_inventory_'.($key+1)]))
                $temp_payload[] = '{ availableQuantity: '.$request[$location['id'].'_inventory_'.($key+1)].', locationId: "'.$location['admin_graphql_api_id'].'" }';
        }
        $str .= implode(',', $temp_payload);
        $str .= ']';
        return $str;
    }
}
