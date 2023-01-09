<?php

namespace App\Models;

use Exception;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Billable;
    
    protected $with = ['getShopifyStore'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'store_id',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getSidebarKey() {
        return $this->id.'-show-sidebar';
    }

    public function loginSecurity() {
        return $this->hasOne(LoginSecurity::class);
    }

    public function getShopifyStore() {
        return $this->hasOne(Store::class, 'table_id', 'store_id');
    }

    public function getPlansInfo() {
        return $this->hasMany(UserPlans::class, 'user_id');
    }

    public function getLastPlanInfo() {
        return $this->hasOne(UserPlans::class, 'user_id')->latest();
    }

    public function getStripeCustomerDetails() {
        if($this->stripe_id !== null && strlen($this->stripe_id) > 0) {
            return $this->stripe_id;
        }
        $customer = $this->createOrGetStripeCustomer();
        $this->update(['stripe_id' => $customer->id]);
        return $customer->id;
    }

    public function assignCredits($credits) {
        try {
            //In a production environment you should use Redis (IMPORTANT)
            //But since this is windows environment I have to use the cache provided by Laravel
            Cache::put('user_credits_'.$this->id, $credits);
            $last_plan = $this->getLastPlanInfo;
            $last_plan->credits = $credits;
            $last_plan->save();
        } catch(Exception $e) {
            Log::info('Problem with assigning credits '.$e->getMessage());
            return true;
        }
    }

    public function getCredits() {
        try {
            $credits = Cache::get('user_credits_'.$this->id);
            if($credits === null) {
                $credits = $this->getLastPlanInfo->credits;
            }
            return $credits;
        } catch(Exception $e) {
            return null;
        }
    }

    public function consumeCredits($consume_credits) {
        try {
            $credits = $this->getCredits();
            if($consume_credits > $credits) 
                throw new Exception('Not enough credits in account.', 429);
            $newCredits = $credits - $consume_credits;
            $this->assignCredits($newCredits);
        } catch(Exception $e) {
            throw $e;
        }
    }

    public function getChannelName($purpose) {
        switch($purpose) {
            case 'messages': return 'receiveNotificationToUser_'.$this->id;
            default: return null;
        }
    }
}
