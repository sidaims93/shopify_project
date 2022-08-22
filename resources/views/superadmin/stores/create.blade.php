@extends('layouts.app')

@section('content')
<section class="section">
  <div class="row">
    <div class="col-lg-8 offset-2">
      <div class="card">
        <div class="card-body">
          {{-- <h5 class="card-title">Add a Private App</h5> --}}
          <!-- General Form Elements -->
          <form method="POST" action="{{route('stores.store')}}">
            @csrf
            <h3 class="pt-4">Store Details</h3>
            <div class="row mb-3 mt-4">
                <label for="inputText" class="col-sm-4 col-form-label">Store URL</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" name="myshopify_domain" value="{{old('myshopify_domain')}}" required>
                  @error('myshopify_domain')
                    <span class="badge bg-danger" >{{$message}}</span>
                  @enderror
                </div>
            </div>
            <div class="row mb-3 mt-4">
                <label for="inputEmail" class="col-sm-4 col-form-label">API Key</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" name="api_key" value="{{old('api_key')}}" required>
                  @error('api_key')
                    <span class="badge bg-danger" >{{$message}}</span>
                  @enderror
                </div>
            </div>
            <div class="row mb-3 mt-4">
                <label for="inputPassword" class="col-sm-4 col-form-label">API Secret Key</label>
                <div class="col-sm-10">
                  <input type="password" class="form-control" name="api_secret_key" value="{{old('api_secret_key')}}" required>
                  @error('api_secret_key')
                    <span class="badge bg-danger" >{{$message}}</span>
                  @enderror
                </div>
            </div>
            <div class="row mb-3 mt-4">
              <label for="inputPassword" class="col-sm-4 col-form-label">Access Token</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="access_token" value="{{old('access_token')}}" required>
                @error('access_token')
                  <span class="badge bg-danger" >{{$message}}</span>
                @enderror
              </div>
            </div>
            <hr>
            <h3>Account Details</h3>
            <div class="row mb-3 mt-4">
              <label for="inputPassword" class="col-sm-4 col-form-label">Account Email</label>
              <div class="col-sm-10">
                <input type="email" class="form-control" name="email" value="{{old('email')}}" required>
                @error('email')
                  <span class="badge bg-danger" >{{$message}}</span>
                @enderror
              </div>
            </div>
            <div class="row mb-3 mt-4">
              <label for="inputPassword" class="col-sm-4 col-form-label">Account Password</label>
              <div class="col-sm-10">
                <input type="password" class="form-control" name="password" value="{{old('password')}}" required>
                @error('password')
                  <span class="badge bg-danger" >{{$message}}</span>
                @enderror
              </div>
            </div>
          
            <div class="row mb-3">
              <div class="col-sm-12 text-center">
                <button type="submit" class="btn btn-primary">Submit</button>
              </div>
            </div>
          </form><!-- End General Form Elements -->
        </div>
      </div>
    </div>
  </div>
</section>
@endsection