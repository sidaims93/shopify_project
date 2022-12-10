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
              <br>
              <div class="row">
                <input type="text" placeholder="Search store here..." class="form-control search_store" style="width:50%">
              </div>
              <br>
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


@section('scripts')
  <script>
    $(document).ready(function () {
      $('.search_store').keyup(function () {
        var el = $(this);
        var val = el.val();
        console.log(val);

        if(val.length > 2) {
          $.ajax({
            url: "{{route('search.store')}}",
            type: 'POST',
            async: false,
            data: {"searchTerm" : val},
            success: function (response) {
              if(response.status) {
                console.log(response);
              } else {
                console.log(response);
              }
            }
          });
        }
      })
    })
  </script>
@endsection