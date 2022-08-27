@extends('layouts.app')

@section('content')
<section class="section">
  <div class="row">
    <div class="col-lg-8 offset-2">
      <div class="card">
        <div class="card-body">
          {{-- <h5 class="card-title">Add a Private App</h5> --}}
          <!-- General Form Elements -->
            <div class="row mb-3 mt-4">
                <label for="inputText" class="col-sm-4 col-form-label">Message</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control" id="message" name="message">
                  @error('myshopify_domain')
                    <span class="badge bg-danger" >{{$message}}</span>
                  @enderror
                </div>
            </div>
            <div class="row mb-3 mt-4">
                <label for="inputEmail" class="col-sm-4 col-form-label">Select Account</label>
                <div class="col-sm-10">
                    <select name="user" id="user" class="form-control">
                        @foreach($users as $user) 
                            <option value="{{$user['id']}}">{{$user['name']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-12 text-center">
                <button id="submit" type="submit" class="btn btn-primary">Send Notification</button>
              </div>
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
            $('#submit').click(function (e) {
                e.preventDefault();
                //console.log(socket);
                var obj = {
                    user: $('#user').val(),
                    message: $('#message').val()
                }
                socket.emit('sendNotificationToUser', obj);
            })
        });
    </script>
@endsection