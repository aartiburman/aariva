
  
  @include('backend.layouts.head')
  @include('backend.layouts.sidebar')
  @include('backend.layouts.header')
  @yield('content')
  @include('backend.layouts.vendor-policy-modal')
  @stack('chart-scripts')
  @include('backend.layouts.footer')
