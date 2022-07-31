@if(Session::has('success'))
    <div class="row">
        <div class="alert alert-primary text-center" style="color:rgb(0, 0, 0)">
            <h5>{{Session::get('success')}}</h5>
        </div>
    </div>
@endif

@if(Session::has('error'))
    <div class="row">
        <div class="alert alert-danger text-center" style="color:rgb(0, 0, 0)">
            <h5>{{Session::get('error')}}</h5>
        </div>
    </div>
@endif