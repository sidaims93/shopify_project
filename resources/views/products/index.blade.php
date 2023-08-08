@extends('layouts.app')
@section('content')
  <div class="pagetitle">
      <div class="row">
          <div class="col-8">
              <h1>Products</h1>
              <nav>
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                    <li class="breadcrumb-item">Products</li>
                  </ol>
              </nav>
          </div>
          <div class="col-4">
            @can('write-products')
            <table class="table table-borderless">
              <tbody>
                <tr>
                  <td><a href="{{route('shopify.product.create')}}" style="float:right" class="btn btn-success">Create Product</a></td>
                  <td><a href="{{route('shopify.products.sync')}}" style="float: right" class="btn btn-primary">Sync Products</a></td>
                </tr>
              </tbody>
            </table>
            @endcan
          </div>
      </div>
  </div><!-- End Page Title -->
  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Your products</h5>
            {{-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> --}}
            <!-- Table with stripped rows -->
            <table class="table datatable">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Name</th>
                  <th scope="col">Type</th>
                  <th scope="col">Vendor</th>
                  <th scope="col">Created Date</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody>
                @isset($products)
                  @if($products !== null)
                    @foreach($products as $key => $product)
                      <tr>
                        <td>{{$key + 1}}</td>
                        <td>{{$product['title']}}</td>
                        <td>{{$product['product_type']}}</td>
                        <td>{{$product['vendor']}}</td>
                        <td>{{date('Y-m-d', strtotime($product['created_at']))}}</td>
                        <td><a href="{{route('change.product.addToCart')}}?product_id={{$product['table_id']}}" class="btn btn-primary">{{$product->getAddToCartStatus()['message']}}</a></td>
                      </tr>
                    @endforeach
                  @endif
                @endisset
              </tbody>
            </table>
            <!-- End Table with stripped rows -->

          </div>
        </div>

      </div>
    </div>
  </section>
@endsection