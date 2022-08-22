@extends('layouts.app')
@section('content')

    <div class="pagetitle">
        <div class="row">
            <div class="col-12">
                <h1>Stores</h1>
                <nav>
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                      <li class="breadcrumb-item">Stores</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              {{-- <h5 class="card-title">Datatables</h5>
              <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> --}}

              <!-- Table with stripped rows -->
              <table class="table datatable">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Public / Private</th>
                    <th scope="col">Myshopify Domain</th>
                    <th scope="col">Created Date</th>
                  </tr>
                </thead>
                <tbody>
                    @isset($stores)
                        @foreach($stores as $store)
                            <tr>
                                <th scope="row">{{$store->id}}</th>
                                <td>{{$store->name}}</td>
                                <td>{{$store->isPublic() ? 'Public' : 'Private'}}</td>
                                <td>{{$store->myshopify_domain}}</td>
                                <td>{{date('F d Y', strtotime($store->created_at))}}</td>
                            </tr>
                        @endforeach
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