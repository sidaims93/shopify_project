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
        @include('layouts.success_message')
        <!-- End Sidebar-->
        @yield('content')
    </main>
    @include('layouts.footer')
    @include('layouts.scripts')
</body>
</html>
