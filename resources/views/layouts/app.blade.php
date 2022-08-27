<!DOCTYPE html>
<html lang="en">
@include('layouts.head')
<body>  
    @include('layouts.header')
    <!-- ======= Sidebar ======= -->
    @role('SuperAdmin')
        @include('superadmin.aside')
    @endrole
    @role('Admin|SubUser')
        @include('layouts.aside')
    @endrole
    <main id="main" class="main">
        @if(Auth::check())
            <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
        @endif
        @include('layouts.success_message')
        <!-- End Sidebar-->
        @yield('content')
    </main>
    @include('layouts.footer')
    @include('layouts.scripts')
    @yield('scripts')
</body>
</html>
