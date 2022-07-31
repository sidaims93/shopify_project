@extends('layouts.app')
@section('content')
    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Team Members</h1>
                <nav>
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                      <li class="breadcrumb-item">Team Members</li>
                    </ol>
                </nav>
            </div>
            <div class="col-4">
                @canany(['all-access', 'write-members'])
                <a href="{{route('members.create')}}" style="float: right" class="btn btn-primary">Add member</a>
                @endcanany
            </div>
        </div>
    </div><!-- End Page Title -->
    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Your team members</h5>
              {{-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> --}}
              <!-- Table with stripped rows -->
              <table class="table datatable">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Name</th>
                    <th scope="col">Role</th>
                    <th scope="col">Permissions</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @isset($members)
                    @if($members !== null)
                      @foreach($members as $key => $member)
                        <tr>
                          <td>{{$key + 1}}</td>
                          <td>{{$member['name']}}</td>
                          <td>{{$member['email']}}</td>
                          <td>{{implode(',', $member->roles->pluck('name')->toArray())}}</td>
                          <td>{{implode(', ', $member->permissions->pluck('name')->toArray())}}</td>
                          <td>{{date('Y-m-d', strtotime($member['created_at']))}}</td>
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