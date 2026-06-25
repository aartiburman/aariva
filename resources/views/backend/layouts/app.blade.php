
  
  @include('backend.layouts.head')
  @include('backend.layouts.sidebar')
  @include('backend.layouts.header')
  @yield('content')
  @include('backend.layouts.vendor-policy-modal')
  @include('backend.layouts.footer')
  @stack('chart-scripts')
