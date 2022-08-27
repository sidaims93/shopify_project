<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
      <li class="nav-item">
        <a class="nav-link " href="{{route('home')}}">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{route('real.time.notifications')}}">
          <i class="bi bi-circle"></i>
          <span>Send Real-time Notifications</span>
        </a>
      </li><!-- End Dashboard Nav -->
      <li class="nav-item">
        <a class="nav-link" data-bs-target="#components-nav" data-bs-toggle="collapse" aria-expanded="true" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Shopify</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
          @canany(['all-access'])
          <li>
            <a href="{{route('stores.index')}}">
              <i class="bi bi-circle"></i><span>Stores</span>
            </a>
          </li>
          <li>
            <a href="{{route('stores.create')}}">
              <i class="bi bi-circle"></i><span>Add Private Store</span>
            </a>
          </li>
          @endcanany
        </ul>
      </li>
    </ul>
</aside>