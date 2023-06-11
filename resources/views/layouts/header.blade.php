@php $isEmbedded = determineIfAppIsEmbedded() @endphp

<header id="header" class="header fixed-top d-flex align-items-center"  @if($isEmbedded) style="background-color:#f1f2f4" @endif>
  <div class="d-flex align-items-center justify-content-between">
    <a href="{{route('home')}}" class="logo d-flex align-items-center">
      <img src="{{asset('assets/img/logo.png')}}" alt="">
      <span class="d-none d-lg-block">{{config('app.name')}}</span>
    </a>
    <i class="bi bi-list toggle-sidebar-btn"></i>
  </div><!-- End Logo -->

  <div class="search-bar">
    {{-- <form class="search-form d-flex align-items-center" method="POST" action="#">
      <input type="text" name="query" placeholder="Search" title="Enter search keyword">
      <button type="submit" title="Search"><i class="bi bi-search"></i></button>
    </form> --}}
  </div><!-- End Search Bar -->

  @include('layouts.nav')<!-- End Icons Navigation -->

</header><!-- End Header -->
