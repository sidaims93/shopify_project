@extends('layouts.app')

@section('content')
<div class="pagetitle">
    <div class="row">
        @if(isset($last_plan_info))
            <div class="col-8 mt-4 mb-4">
                <h5>Your remaining credits: @if(isset($credits) && $credits !== null && $credits !== false) {{$credits}} @else {{$last_plan_info->credits}} @endif</h5>
            </div> 
        @endif
        <div class="row">
            <div class="col-8">
                <h1>Subscriptions</h1>
            </div>
            <div class="col-4">
                <a style="float:right" href="{{route('billing.portal')}}" class="btn btn-md btn-primary">View Invoices Info</a>
            </div>    
        </div>
    </div>
    @if(!$user->hasPaymentMethod())
    <div class="row">
        <div class="col-12 text-center">
            <a href="#" class="btn btn-primary add_card">Add Card</a>
        </div>
        <div class="col-12 text-center card_div" style="display:none">
            <div class="col-6 offset-md-3 card">
                <input id="card-holder-name" placeholder="Enter your name" class="form-control" type="text">
                <br>
                
                <!-- Stripe Elements Placeholder -->
                <div id="card-element"></div>
                
                <br>
                <button id="card-button" class="btn btn-md btn-success" data-secret="{{ $intent->client_secret }}">
                    Update Payment Method
                </button>
            </div>   
        </div>
    </div>
    @endif
    @isset($plans)
    <div class="row col-12 mt-4">
        @foreach($plans as $plan)
            <div class="col-4">
                <div class="card">
                    <div class="card-title text-center">{{ucwords($plan['stripe_plan_id'])}}</div>
                    <div class="card-body text-center">
                        <p>${{$plan['price']}}</p>
                        <p>{{$plan['credits']}} Credits</p>
                        <div class="text-center col-12">
                            @if(isset($last_plan_info) && $last_plan_info->plan_id === $plan['id']) 
                            <a href="#" class="btn btn-success" style="width: 100%">Active</a>
                            @else
                            <a href="{{route('purchase.subscription', $plan['id'])}}" class="btn btn-primary" style="width: 100%">Buy</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endisset
    <div class="row">
        <div class="col-8">
            <h1>One-time payments</h1>
        </div>
        <div class="row col-12 mt-4">
            @foreach($one_time_payments as $id => $payment)
            <div class="col-4">
                <div class="card">
                    <div class="card-title text-center">{{$payment['name']}}</div>
                    <div class="card-body text-center">
                        <p>${{$payment['price']}}</p>
                        <p>{{$payment['credits']}} Credits</p>
                        <div class="text-center col-12">
                            <a href="{{route('purchase.credits', $id)}}" class="btn btn-warning text-white" style="width: 100%">Purchase</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
 
<script>
    const stripe = Stripe('{{$stripe_key}}');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');
</script>

<script>
    $(document).ready(function () {
        $('.add_card').click(function (e) {
            e.preventDefault();
            $('.card_div').css({'display':'block'});
        })

        if($('.card_div').length) {
            const cardHolderName = document.getElementById('card-holder-name');
            const cardButton = document.getElementById('card-button');
            const clientSecret = cardButton.dataset.secret;
            
            cardButton.addEventListener('click', async (e) => {
                const { setupIntent, error } = await stripe.confirmCardSetup(
                    clientSecret, {
                        payment_method: {
                            card: cardElement,
                            billing_details: { 
                                name: cardHolderName.value, 
                                email: '{{Auth::user()->email}}', 
                            }
                        }
                    }
                );
            
                if (error) {
                    // Display "error.message" to the user...
                    console.log(error);
                } else {
                    $.ajax({
                        type: 'POST',
                        url: '{{route('add.card.user')}}',
                        async: false,
                        data: { 'card_details': setupIntent },
                        success: function (response) {
                            console.log(response);
                        }
                    })
                    // The card has been verified successfully...
                }
            });
        }
        
    })
</script>
@endsection