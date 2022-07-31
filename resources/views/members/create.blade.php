@extends('layouts.app')
@section('content')
    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Add Team Member</h1>
                <nav>
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                      <li class="breadcrumb-item">Add Team Member</li>
                    </ol>
                </nav>
            </div>
            <div class="col-4">
                <a href="{{url()->previous()}}" style="float: right" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div><!-- End Page Title -->
    <section class="section">
      <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-body mt-4">
                  {{-- <h5 class="card-title">Please fill the below form</h5> --}}
                  <!-- Multi Columns Form -->
                  <form class="row g-3" method="POST" action="{{route('members.store')}}">
                    @csrf
                    <div class="col-md-6">
                      <label for="name" class="form-label" style="font-weight:bold">Name</label>
                      <input type="text" autofocus class="form-control" id="name" name="name" value="{{old('name')}}" required>
                      @error('name')
                        <span class="badge border-primary border-1 text-danger">{{$message}}</span>
                      @enderror
                    </div>
                    <div class="col-md-6">
                      <label for="email" class="form-label" style="font-weight:bold">Email</label>
                      <input type="email" class="form-control" id="email" name="email" value="{{old('email')}}" required>
                        @error('email')
                        <span class="badge border-primary border-1 text-danger">{{$message}}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                      <label for="password" class="form-label" style="font-weight:bold">Password</label>
                      <input type="password" class="form-control" id="password" name="password" required>
                      @error('password')
                        <span class="badge border-primary border-1 text-danger">{{$message}}</span>
                      @enderror
                    </div>
                    <div class="col-6">
                      <label for="confirm-password" class="form-label" style="font-weight:bold">Confirm Password</label>
                      <input type="password" class="form-control" id="confirm-password" name="password_confirmation" required>
                    </div>
                    @error('permissions')
                        <span class="badge border-primary border-1 text-danger">{{$message}}</span>
                    @enderror
                    <div class="col-8 offset-2">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                  <th scope="col">Permission</th>
                                  <th scope="col" class="text-center">Read</th>
                                  <th scope="col" class="text-center">Write</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Products</td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" id="gridCheck" name="permissions[]" value="read-products">
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" id="gridCheck" name="permissions[]" value="write-products">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Customers</td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" id="gridCheck" name="permissions[]" value="read-customers">
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" id="gridCheck" name="permissions[]" value="write-customers">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Orders</td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" id="gridCheck" name="permissions[]" value="read-orders">
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" id="gridCheck" name="permissions[]" value="write-orders">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Team Members</td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" id="gridCheck" name="permissions[]" value="read-members">
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" id="gridCheck" name="permissions[]" value="write-members">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                      <button type="submit" class="btn btn-primary">Pay $2 and Submit</button>
                      {{-- <button type="reset" class="btn btn-secondary">Reset</button> --}}
                    </div>
                  </form><!-- End Multi Columns Form -->
                </div>
              </div>
        </div>
      </div>
    </section>
@endsection