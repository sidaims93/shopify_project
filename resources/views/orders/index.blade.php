@extends('layouts.app')
@section('content')

    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Orders</h1>
                <nav>
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                      <li class="breadcrumb-item">Orders</li>
                    </ol>
                </nav>
            </div>
            <div class="col-4">
              @can('write-orders')
                <a href="{{route('orders.sync')}}" style="float: right" class="btn btn-primary">Sync Orders</a>
              @endcan
            </div>
        </div>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Orders</h5>
              <!-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> -->

              <!-- Table with stripped rows -->
              <table class="table datatable">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Customer Email</th>
                    <th scope="col" class="text-center">Payment Status</th>
                    <th scope="col">Customer Phone</th>
                    <th scope="col">Created Date</th>
                  </tr>
                </thead>
                <tbody>
                  @isset($orders)
                    @foreach($orders as $key => $order)
                    <tr>
                      <td>{{$key + 1}}</td>
                      <td><a class="btn-link" href="{{route('shopify.order.show', $order->table_id)}}">{{$order->name}}</a></td>
                      <td>{{$order->email}}</td>
                      <td class="text-center">{{$order->getPaymentStatus()}}</td>
                      <td>{{$order->phone}}</td>
                      <td>{{date('Y-m-d h:i:s', strtotime($order->created_at))}}</td>
                    </tr>
                    @endforeach
                  @endisset
                </tbody>
              </table>
              <div class="text-center pb-2">
                {{$orders->links()}}
              </div>
              <!-- End Table with stripped rows -->
            </div>
          </div>
        </div>
      </div>
    </section>
@endsection