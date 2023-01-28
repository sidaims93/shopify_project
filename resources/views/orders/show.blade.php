@extends('layouts.app')

@section('content')
<div class="pagetitle">
    <div class="row">
        <div class="col-12">
            <div class="col-9">
                <h1>Order {{$order->name}}</h1>
                <nav>
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{route('shopify.orders')}}">Orders</a></li>
                    <li class="breadcrumb-item">Order {{$order->name ?? ''}}</li>
                    </ol>
                </nav>
            </div>
            <div class="col-3" style="float:right">
                <select id="inputState" class="form-select actions">
                    <option selected="" disabled>Actions</option>
                    <option value="fulfill_items">Fulfill Items</option>
                </select>
            </div>
        </div>
    </div>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-lg-12">
            <div class="">
                <div class="col-xxl-12 col-md-12">
                    <div class="card info-card sales-card">
                        <div class="card-body pb-0 mt-2">
                            <h5 class="card-title">Order Details</h5>        
                            <table class="table table-borderless">
                                <thead>
                                    <th>Payment Status</th>
                                    <th class="text-center">Fulfillment Status</th>
                                    <th style="float:right">Order Date</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{$order->getPaymentStatus()}}</td>
                                        <td class="text-center">{{$order->getFulfillmentStatus()}}</td>
                                        <td style="float: right;">{{date('F d, Y', strtotime($order['created_at_date']))}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 items_card">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Items</h5>
                    <input type="hidden" id="order_id" value="{{$order['table_id']}}">
                    @if($order['line_items'] && is_array($order['line_items']) && count($order['line_items']) > 0)
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body pt-2">
                                <table class="table table-borderless">
                                    <thead>
                                        <th style="width:10%"></th>
                                        <th>Product</th>
                                        <th style="width:20%">Price X Qty</th>
                                        <th style="width:15%">Total</th>
                                        <th style="display: none;" class="fulfill-th"></th>
                                    </thead>
                                    <tbody>
                                        @foreach ($order['line_items'] as $item)
                                        <tr>
                                            <td>
                                                @isset($product_images)
                                                    @isset($product_images[$item['product_id']])
                                                    <div class="img image-responsive">
                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#imagesmodal-{{$item['product_id']}}">
                                                            <img height="55px" width="auto" src="{{$product_images[$item['product_id']][0]['src'] ?? null}}" alt="Image here">
                                                        </a>
                                                    </div>
                                                    @endisset
                                                @endisset
                                            </td>
                                            <td>
                                                {{$item['title']}}<br>
                                                <small class="text-muted">SKU: {{$item['sku'] ?? ''}}</small> |
                                                <small class="text-muted">Variant: {{$item['variant_title'] ?? ''}}</small> <br>
                                            </td>
                                            <td> {{$order_currency}} {{number_format($item['price'], 2)}} <span>x</span> {{$item['quantity']}} </td>
                                            <td>
                                                @php $sub_price = number_format((double) $item['price'] * (double) $item['quantity'], 2); @endphp
                                                {{$order_currency}} {{$sub_price}}
                                            </td>
                                            <td style="display: none;" class="fulfill-th">
                                                @if($item['fulfillable_quantity'] > 0)
                                                    @if($item['fulfillment_service'] === 'manual' || $item['fulfillment_service'] === 'app-fulifllment-service')
                                                        <a href="#" class="btn btn-primary fulfill_this_item" data-line_item_id="{{$item['id']}}" data-qty="{{$item['quantity']}}">Fulfill</a>
                                                    @else
                                                        <span class="badge bg-danger">Un-fulfillable</span>
                                                    @endif
                                                @else 
                                                    <span class="badge bg-success">Fulfilled</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table> 
                            </div>
                        </div>
                        <div class="card-footer">
                            <table class="table table-hover table-xl mb-0 total-table">
                                <tbody>
                                    @if(!empty($order->getDiscountBreakDown()))
                                        @foreach($order->getDiscountBreakDown() as $title => $discount)
                                            <tr>
                                                <td class="text-truncate text-left">Discount Code</td>
                                                <td class="text-truncate text-left"><b>{{$title ?? ''}}</b></td>
                                                <td class="text-truncate text-right"><span style="float:right">- {{$order_currency.' '.number_format($discount, 2)}}</span></td>
                                            </tr>
                                        @endforeach
                                    @if(count($order->getDiscountBreakDown()) > 1)    
                                    <tr>
                                        <td class="text-truncate text-left">Total Discount</td>
                                        <td class="text-truncate text-left"></td>
                                        <td class="text-truncate text-right"><span style="float:right">- {{$order_currency.' '.number_format($order->total_discounts, 2)}}</span></td>
                                    </tr>
                                    @endif
                                    @endif
                                    <tr>
                                        <td class="text-truncate text-left">Subtotal</td>
                                        <td class="text-truncate text-left">{{count($order['line_items'])}} {{count($order['line_items']) > 1 ? 'Items' : 'Item' }}</td>
                                        <td class="text-truncate text-right"><span style="float:right">{{$order_currency}} {{number_format($order['subtotal_price'], 2)}}</span></td>
                                    </tr> 
                                    @if(!empty($order['shipping_lines']))
                                        @php $total_shipping = 0; @endphp
                                        @foreach($order['shipping_lines'] as $ship)
                                            <tr>
                                                <td class="text-truncate text-left">Shipping</td>
                                                <td class="text-truncate text-left">{{strlen($ship['title']) < 20 ? $ship['title'] : 'Standard Shipping'}}</td>
                                                <td class="text-truncate text-right"><span style="float:right">{{$order_currency.' '.number_format($ship['price'], 2)}}</span></td>
                                            </tr>
                                        @php $total_shipping += $ship['price']; @endphp
                                        @endforeach
                                        @if(!empty($order['shipping_lines']) && count($order['shipping_lines']) > 0)
                                        <tr>
                                            <td class="text-truncate text-left">Total Shipping</td>
                                            <td class="text-truncate text-left"></td>
                                            <td class="text-truncate text-right"><span style="float:right">{{$order_currency.' '.number_format($total_shipping, 2)}}</span></td>
                                        </tr>
                                        @endif
                                    @endif
                                    @if(!empty($order['tax_lines']))
                                        @foreach($order['tax_lines'] as $tax)
                                            <tr>
                                                <td class="text-truncate text-left">Tax</td>
                                                <td class="text-truncate text-left">{{$tax['title'].' ( '.((float)$tax['rate'] * 100).'% )'}}</td>
                                                <td class="text-truncate text-right"><span style="float:right">{{$order_currency.' '.number_format($tax['price'], 2)}}</span></td>
                                            </tr>
                                        @endforeach
                                        @if(!empty($order['tax_lines']) && count($order['tax_lines']) > 1)    
                                        <tr>
                                            <td colspan="2" class="text-truncate">Total Taxes @if($order['taxes_included'] == true) (Inclusive) @endif</td>
                                            <td class="text-truncate text-left"></td>
                                            <td class="text-truncate text-right"><span style="float:right">{{$order_currency.' '.number_format($order['total_tax'], 2)}}</span></td>
                                        </tr>
                                        @endif
                                    @endif
                                    <tr>
                                        <td class="text-truncate text-left text-bold">TOTAL AMOUNT</td>
                                        <td class="text-truncate text-left"></td>
                                        <td class="text-truncate text-right text-bold"><span style="float:right">{{$order_currency.' '}}{{number_format( $order['total_price'], 2)}}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>
        @include('modals.fulfill_item')
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Shipping Address</h5>
                    <div class="alert alert-light" role="alert">
                        <p>
                            {{$order['shipping_address']['name'] ?? ''}} <br>
                            {{$order['shipping_address']['phone'] ?? ''}} <br>
                            {{$order['shipping_address']['address1'] ?? ''}} <br>
                            {{$order['shipping_address']['address2'] ?? ''}} <br>
                            {{$order['shipping_address']['province'] ?? ''}} {{$order['shipping_address']['city']}} <br>
                            {{$order['shipping_address']['country'] ?? ''}} {{$order['shipping_address']['zip'] ?? ''}}
                        </p>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Billing Address</h5>
                    <div class="alert alert-light" role="alert">
                        <p>
                            {{$order['billing_address']['name'] ?? ''}} <br>
                            {{$order['billing_address']['phone'] ?? ''}} <br>
                            {{$order['billing_address']['address1'] ?? ''}} <br>
                            {{$order['billing_address']['address2'] ?? ''}} <br>
                            {{$order['billing_address']['province'] ?? ''}} {{$order['billing_address']['city']}} <br>
                            {{$order['billing_address']['country'] ?? ''}} {{$order['billing_address']['zip'] ?? ''}}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.actions').change(function () {
                var val = $(this).val();
                if(val == 'fulfill_items') {
                    $('.items_card').removeClass('col-lg-8').addClass('col-lg-12');
                    $('.fulfill-th').css({'display':'block'});
                    $('.fulfill-td').css({'display':'block'});
                }
            });

            $('.fulfill_this_item').click(function () {
                var lineItemId = $(this).data('line_item_id');
                $('.fulfill_submit').css({'display':'block'});
                $('.fulfill_loading').css({'display': 'none'});
                $('#lineItemId').val(parseInt(lineItemId));
                var qty = parseInt($(this).data('qty'));
                var select_html = '';
                for(var i = 1; i <= qty; i++) {
                    select_html += "<option value="+i+">"+i+"</option>";
                }   
                $('#no_of_packages').html(select_html);
                $('.fulfillment_form').find('input:text').val('');
                $('.fulfillment_form').find('input:checkbox').prop('checked', false);
                $('#fulfill_items_modal').modal('show');
            });

            $('.fulfill_submit').click(function (e) {
                e.preventDefault();
                $(this).attr('disabled', true);
                $('.fulfill_submit').css({'display':'none'});
                $('.fulfill_loading').removeAttr('style');
                var data = {};
                $('.fulfillment_form').find('[id]').each( function(i, v){
                    var input = $(this); // resolves to current input element.
                    data[input.attr('id')] = input.val();
                });
                data['order_id'] = $('#order_id').val();
                data['lineItemId'] = $('#lineItemId').val();
                data['notify_customer'] = $('#notify_customer').prop('checked') ? 'on':'off';
                $.ajax({
                    type: 'POST',
                    url: "{{route('shopify.order.fulfill')}}",
                    data: data,
                    async: false,
                    success: function (response) {
                        console.log(response);
                        //window.top.location.reload();
                    }
                });
            });
        });
    </script>
@endsection