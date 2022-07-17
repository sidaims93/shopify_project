<!DOCTYPE html>
<html lang="en">
@include('layouts.head')
<body>  
    @include('layouts.header')
    <!-- ======= Sidebar ======= -->
    @include('layouts.aside')
    <!-- End Sidebar-->
    @yield('content')
    @include('layouts.scripts')
</body>
</html>
