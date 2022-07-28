<!DOCTYPE html>
<html lang="en">
@include('layouts.head')
<body>  
    @include('layouts.header')
    <!-- ======= Sidebar ======= -->
    @include('layouts.aside')
    <main id="main" class="main">
        @include('layouts.success_message')
        <!-- End Sidebar-->
        @yield('content')
    </main>
    @include('layouts.scripts')
</body>
</html>
