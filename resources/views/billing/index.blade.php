@extends('layouts.app')

@section('content')
<div class="pagetitle">
    <div class="row">
        <div class="col-8">
            <h1>Billing</h1>
        </div>
        @if(isset($last_plan_info))
            <div class="col-8 mt-4">
                <h5>Your remaining credits: @if(isset($credits) && $credits !== null && $credits !== false) {{$credits}} @else {{$last_plan_info->credits}} @endif</h5>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-12 text-center">
            <a href="{{route('consume.credits')}}" class="btn btn-primary">Consume Credits</a>
        </div>
    </div>
    @isset($plans)
    <div class="row col-12 mt-4">
        @foreach($plans as $plan)
            <div class="col-4">
                <div class="card">
                    <div class="card-title text-center">{{$plan['name']}}</div>
                    <div class="card-body text-center">
                        <p>${{$plan['price']}}</p>
                        <p>{{$plan['credits']}} Credits</p>
                        <div class="text-center col-12">
                            @if(isset($last_plan_info) && $last_plan_info->plan_id === $plan['id']) 
                            <a href="#" class="btn btn-success" style="width: 100%">Active</a>
                            @else
                            <a href="{{route('plan.buy', $plan['id'])}}" class="btn btn-primary" style="width: 100%">Buy</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endisset
</div>
@endsection