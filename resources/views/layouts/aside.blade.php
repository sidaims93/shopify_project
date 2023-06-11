@php $isEmbedded = determineIfAppIsEmbedded() @endphp

<aside id="sidebar" class="sidebar" @if($isEmbedded) style="background-color:#f1f2f4" @endif>

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="{{route('home')}}">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link" data-bs-target="#components-nav" data-bs-toggle="collapse" aria-expanded="true" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Shopify</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
          @canany(['all-access','write-orders','read-orders'])
          <li>
            <a href="{{route('shopify.orders')}}">
              <i class="bi bi-circle"></i><span>Orders</span>
            </a>
          </li>
          @endcanany
          @canany(['all-access','write-products','read-products'])
          <li>
            <a href="{{route('shopify.products')}}">
              <i class="bi bi-circle"></i><span>Products</span>
            </a>
          </li>
          @endcanany
          @canany(['all-access','write-customers','read-customers'])
          <li>
            <a href="{{route('shopify.customers')}}">
              <i class="bi bi-circle"></i><span>Customers</span>
            </a>
          </li>
          @endcanany
        </ul>
      </li><!-- End Components Nav -->
      @if(Auth::user()->getShopifyStore->isPublic())
        @canany(['all-access','write-members','read-members'])
        <li class="nav-item">
          <a class="nav-link collapsed" href="{{route('members.index')}}">
            <i class="bi bi-people"></i>
            <span>My Team</span>
          </a>
        </li><!-- End Contact Page Nav -->
        @endcanany
        @role('Admin')
        <li class="nav-item">
          <a class="nav-link collapsed" href="{{route('billing.index')}}">
            <i class="bi bi-cash"></i>
            <span>Billing</span>
          </a>
        </li>
        @endrole

      @else 
      @role('Admin')
        <li class="nav-item">
          <a class="nav-link collapsed" href="{{route('subscriptions.index')}}">
            <i class="bi bi-cash"></i>
            <span>Subscriptions</span>
          </a>
        </li>
        @endrole
      @endif
      <li class="nav-item">
        <a class="nav-link collapsed" href="{{route('show2FASettings')}}">
          <i class="bi bi-wrench"></i>
          <span>Security</span>
        </a>
      </li><!-- End Contact Page Nav -->
      
      <li class="nav-item">
        <a class="nav-link collapsed"  onclick="event.preventDefault(); document.getElementById('logout-user').submit();">
          <i class="bi bi-box-arrow-right"></i>
            <form id="logout-user" action="{{ route('logout') }}" method="POST" class="d-none" style="display: none">
                @csrf
            </form>
          <span>Sign Out</span>
        </a>
      </li><!-- End Blank Page Nav -->

    </ul>

  </aside>