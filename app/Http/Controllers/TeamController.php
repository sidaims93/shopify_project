<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMember;
use App\Models\User;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Exceptions\UnauthorizedException;

class TeamController extends Controller {
    use RequestTrait;

    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        $user = Auth::user();
        $members = User::with(['roles', 'permissions'])
                        ->where('store_id', $user->store_id)
                        ->whereHas('roles', function ($q) { return $q->where('name', 'SubUser');})
                        ->select(['id', 'name', 'email', 'created_at'])
                        ->get();
        return view('members.index', ['members' => $members]);
    }

    public function create() {
        $user = Auth::user();
        if($user->can(['write-members']));
            return view('members.create');
        throw new UnauthorizedException(403, 'You are not authorised to view this page.');
    }

    public function store(CreateMember $request) {
        try {
            $request = $request->all();
            $user = Auth::user();
            $store = $user->getShopifyStore;

            DB::beginTransaction();
            $subuser = User::updateOrCreate([
                'email' => $request['email']
            ], [
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
                'store_id' => $user->store_id
            ]);
            $subuser->assignRole('SubUser');
            $subuser->markEmailAsVerified(); //To mark this user verified without requiring them to.
            foreach($request['permissions'] as $permission)
                if(in_array($permission, config('custom.default_permissions')))
                    $subuser->givePermissionTo($permission);

            $endpoint = getShopifyURLForStore('application_charges.json', $store);
            $headers = getShopifyHeadersForStore($store);
            $payload = [
                'application_charge' => [
                    'name' => 'Added sub user '.$request['email'],
                    'price' => 2.0,
                    'test'=> 'true',
                    'return_url' => config('app.url').'shopify/accept/charge?user_id='.$subuser->id
                ]
            ];
            $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);
            if($response['statusCode'] === 201) {
                $body = $response['body']['application_charge'];
                DB::commit();
                return redirect($body['confirmation_url']);
            }
            DB::rollBack();
            return back()->with('error', 'Sub user '.$request['email'].' could not be created.');
        } catch(Exception $e) {
            return back()->with('error', $e->getMessage().' '.$e->getLine());
        }   
    }

    public function show() {

    }

    public function destroy() {

    }
}
