@extends('layouts.app')
@section('css')
  <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
@endsection
@section('content')

    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Customers</h1>
                <nav>
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                      <li class="breadcrumb-item">Customers</li>
                    </ol>
                </nav>
            </div>
            <div class="col-4">
              @can('write-customers')
                <a href="{{route('customers.sync')}}" style="float: right" class="btn btn-primary">Sync Customers</a>
              @endcan
            </div>
        </div>
    </div><!-- End Page Title -->
    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Your Customers</h5>

              <!-- Table with stripped rows -->
              <table class="" id="dt-table">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Created On</th>
                  </tr>
                </thead>
                <tbody>
                  
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

<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>

<script>
  $('#dt-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{route('customers.list')}}',
    columns: [
      {data: '#', name: '#'},
      {data: 'first_name', name: 'first_name'},
      {data: 'email', name: 'email'},
      {data: 'phone', name: 'phone'},
      {data: 'created_at', name: 'created_at'}
    ]
  });
</script>
@endsection