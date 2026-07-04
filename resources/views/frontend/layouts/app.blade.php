@php
    use App\Models\Cart;
    use App\Models\Country;
    use App\Models\Wishlist;

    $cartUserId = Auth::check() ? Auth::id() : null;
    $cartIp = request()->ip();
    $headerCartItems = Cart::with(['product', 'variant'])
        ->when($cartUserId, function ($q) use ($cartUserId) {
            $q->where('user_id', $cartUserId);
        }, function ($q) use ($cartIp) {
            $q->where('ip_address', $cartIp);
        })
        ->get();
    $cartCount = $headerCartItems->sum('qty');
    $cartTotalPrice = $headerCartItems->sum(function ($ci) {
        return ($ci->variant->price ?? $ci->price ?? 0) * $ci->qty;
    });
    
    $wishlistCount = Wishlist::when($cartUserId, function ($q) use ($cartUserId) {
        $q->where('user_id', $cartUserId);
    }, function ($q) use ($cartIp) {
        $q->where('ip_address', $cartIp);
    })->count();

    $headerCountries = Cache::remember('countries.active', 3600, function () {
        return Country::where('is_active', 1)->orderBy('name')->get();
    });
@endphp
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('frontend/assets/images/favicon-32x32.png') }}" type="image/png" />

    <!-- SEO Meta Tags -->
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', 'Discover endless collection at ' . config('app.name') . ' - Your one-stop shop for fashion, electronics, beauty & more. Unbeatable deals, fast delivery.')">
    <meta name="keywords" content="@yield('meta_keywords', 'Aariva, one store endless collection, online shopping, fashion, electronics, beauty, ecommerce, buy online, best deals')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())" />
    <link rel="alternate" href="{{ url()->current() }}?lang=en" hreflang="x-default" />
    <link rel="alternate" href="{{ url()->current() }}?lang=en" hreflang="en" />
    <link rel="alternate" href="{{ url()->current() }}?lang=ar" hreflang="ar" />
    <link rel="alternate" href="{{ url()->current() }}?lang=hi" hreflang="hi" />
    {!! $__env->yieldPushContent('head-links') !!}

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:description" content="@yield('og_description', 'Discover endless collection at ' . config('app.name') . ' - Your one-stop shop for fashion, electronics, beauty & more. Unbeatable deals, fast delivery.')">
    <meta property="og:image" content="@yield('og_image', asset('frontend/assets/images/favicon-32x32.png'))">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="en_US">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="@yield('og_url', url()->current())">
    <meta name="twitter:title" content="@yield('og_title', config('app.name'))">
    <meta name="twitter:description" content="@yield('og_description', 'Discover endless collection at ' . config('app.name') . ' - Your one-stop shop for fashion, electronics, beauty & more. Unbeatable deals, fast delivery.')">
    <meta name="twitter:image" content="@yield('og_image', asset('frontend/assets/images/favicon-32x32.png'))">

    {!! $__env->yieldPushContent('pagination-links') !!}

    <script type="application/ld+json">
    {
        "<?php echo '@'; ?>context": "https://schema.org",
        "<?php echo '@'; ?>type": "Organization",
        "name": "{{ config('app.name') }}",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('frontend/assets/images/favicon-32x32.png') }}",
        "contactPoint": {
            "<?php echo '@'; ?>type": "ContactPoint",
            "telephone": "+1-555-555-5555",
            "contactType": "customer service"
        },
        "sameAs": [
            "https://facebook.com/{{ config('app.name') }}",
            "https://twitter.com/{{ config('app.name') }}",
            "https://instagram.com/{{ config('app.name') }}"
        ]
    }
    </script>

    <script type="application/ld+json">
    {
        "<?php echo '@'; ?>context": "https://schema.org",
        "<?php echo '@'; ?>type": "WebSite",
        "name": "{{ config('app.name') }}",
        "url": "{{ url('/') }}",
        "potentialAction": {
            "<?php echo '@'; ?>type": "SearchAction",
            "target": {
                "<?php echo '@'; ?>type": "EntryPoint",
                "urlTemplate": "{{ route('frontend.products.index') }}?search={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <link href="{{ asset('frontend/assets/plugins/OwlCarousel/css/owl.carousel.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('frontend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('frontend/assets/css/pace.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('frontend/assets/js/pace.min.js') }}"></script>
    <link href="{{ asset('frontend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
    <link href="{{ asset('frontend/assets/css/app.css') }}" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link href="{{ asset('frontend/assets/css/icons.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {!! $__env->yieldPushContent('styles') !!}
    <style>
    .search-results-dropdown {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 9999;
        background: #fff; border: 1px solid #dee2e6; border-top: none;
        max-height: 420px; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .search-result-item {
        display: flex; align-items: center; gap: 10px; padding: 8px 12px;
        cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: background 0.15s;
    }
    .search-result-item:hover { background: #f8f9fa; }
    .search-result-item img { width: 40px; height: 40px; object-fit: cover; border-radius: 4px; }
    .search-result-item .result-info { flex: 1; min-width: 0; }
    .search-result-item .result-info .result-name { font-size: 14px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .search-result-item .result-info .result-meta { font-size: 12px; color: #6c757d; }
    .search-result-item .result-price { font-size: 14px; font-weight: 600; color: #212529; white-space: nowrap; }
    .search-result-section { padding: 6px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6c757d; background: #f8f9fa; border-bottom: 1px solid #e9ecef; letter-spacing: 0.5px; }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="header-wrapper fixed-header">
            <div class="top-menu">
                <div class="container">
                    <nav class="navbar navbar-expand">
                        <div class="shiping-title d-none d-sm-flex">{{ __t('Welcome to our ' . config('app.name') . ' store!') }}</div>
                        <ul class="navbar-nav ms-auto d-none d-lg-flex">
                            <li class="nav-item"><a class="nav-link" href="{{ route('frontend.order-tracking') }}">{{ __t('Track Order') }}</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('frontend.about-us') }}">{{ __t('About') }}</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('frontend.products.index') }}">{{ __t('Our Stores') }}</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('frontend.blog-post') }}">{{ __t('Blog') }}</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('frontend.contact-us') }}">{{ __t('Contact') }}</a></li>
                            <li class="nav-item"><a class="nav-link" href="javascript:;">{{ __t('Help & FAQs') }}</a></li>
                        </ul>
                        <ul class="navbar-nav">
                            @auth
                            <li class="nav-item">
                                    <a class="nav-link" href="{{ route('frontend.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __t('Logout') }}</a>
                                <form id="logout-form" action="{{ route('frontend.logout') }}" method="POST" class="d-none">@csrf</form>
                            </li>
                            @endauth
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                                    <div class="lang d-flex gap-1">
                                        <div><i class="flag-icon flag-icon-{{ strtolower(session('country_code', 'IN')) }}"></i></div>
                                        <div><span>{{ strtoupper(session('currency_code', 'INR')) }}</span></div>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-lg-end">
                                    @foreach($headerCountries as $hc)
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', $hc->shortname) }}">
                                        <i class="flag-icon flag-icon-{{ strtolower($hc->shortname) }} me-2"></i>
                                        <span>{{ __t($hc->name . ' (' . $hc->currency_code . ')') }}</span>
                                    </a>
                                    @endforeach
                                </div>
                            </li>
                            @if(session('country_code') === 'IN')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                                    <i class='bx bx-globe me-1'></i>
                                    <span>
                                        @switch(session('locale', 'en'))
                                            @case('hi') हिन्दी @break
                                            @case('hing') Hinglish @break
                                            @default English
                                        @endswitch
                                    </span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-lg-end">
                                    <a class="dropdown-item {{ session('locale') === 'en' ? 'active' : '' }}" href="{{ route('change.language', 'en') }}">
                                        <i class='bx bx-check me-2 {{ session('locale') === 'en' ? '' : 'invisible' }}'></i> English
                                    </a>
                                    <a class="dropdown-item {{ session('locale') === 'hi' ? 'active' : '' }}" href="{{ route('change.language', 'hi') }}">
                                        <i class='bx bx-check me-2 {{ session('locale') === 'hi' ? '' : 'invisible' }}'></i> हिन्दी
                                    </a>
                                    <a class="dropdown-item {{ session('locale') === 'hing' ? 'active' : '' }}" href="{{ route('change.language', 'hing') }}">
                                        <i class='bx bx-check me-2 {{ session('locale') === 'hing' ? '' : 'invisible' }}'></i> Hinglish
                                    </a>
                                </div>
                            </li>
                            @endif
                        </ul>
                        <ul class="navbar-nav social-link ms-lg-2 ms-auto">
                            <li class="nav-item"> <a class="nav-link" href="javascript:;"><i class='bx bxl-facebook'></i></a></li>
                            <li class="nav-item"> <a class="nav-link" href="javascript:;"><i class='bx bxl-twitter'></i></a></li>
                            <li class="nav-item"> <a class="nav-link" href="javascript:;"><i class='bx bxl-linkedin'></i></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="header-content bg-warning">
                <div class="container">
                    <div class="row align-items-center gx-4">
                        <div class="col-auto">
                            <div class="d-flex align-items-center gap-3">
                                <div class="mobile-toggle-menu d-inline d-xl-none" data-bs-toggle="offcanvas"
                                    data-bs-target="#offcanvasNavbar">
                                    <i class="bx bx-menu"></i>
                                </div>
                                <div class="logo">
                                    <a href="{{ route('frontend.home') }}">
                                        <img src="{{ App\Helpers\ImageHelper::getWebsiteLogo($siteLogoDark) }}" class="logo-icon" alt="{{ $siteName }}" />
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-xl order-4 order-xl-0 position-relative">
                            <form action="{{ route('frontend.products.index') }}" method="GET" class="input-group flex-nowrap pb-3 pb-xl-0" autocomplete="off">
                                <input type="text" name="search" id="headerSearchInput" class="form-control w-100 border-dark border border-3" placeholder="Search for Products" value="{{ request('search') }}">
                                <button class="btn btn-dark btn-ecomm border-3" type="submit" id="headerSearchBtn">{{ __t('Search') }}</button>
                            </form>
                            <div id="searchResults" class="search-results-dropdown d-none"></div>
                        </div>
                        <div class="col-auto d-none d-xl-flex">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="fs-1 text-content"><i class='bx bx-headphone'></i></div>
                                    <div class="">
                                        <p class="mb-0 text-content">{{ __t('CALL US NOW') }}</p>
                                        <h5 class="mb-0">{{ $contactPhone ?? '+011 5827918' }}</h5>
                                    </div>
                                </div>
                        </div>
                        <div class="col-auto ms-auto">
                            <div class="top-cart-icons">
                                <nav class="navbar navbar-expand">
                                    <ul class="navbar-nav">
                                        @auth
                                        <li class="nav-item"><a href="{{ route('frontend.user.profile') }}" class="nav-link cart-link"><i class='bx bx-user'></i></a></li>
                                        @else
                                        <li class="nav-item"><a href="javascript:;" class="nav-link cart-link" data-bs-toggle="modal" data-bs-target="#authModal" data-auth-tab="login"><i class='bx bx-user'></i></a></li>
                                        @endauth
                                        <li class="nav-item">
    <a href="{{ route('frontend.wishlist.index') }}" class="nav-link cart-link position-relative">
        <span class="alert-count wishlist-count">{{ $wishlistCount }}</span>
        <i class='bx bx-heart'></i>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('frontend.cart.index') }}" class="nav-link position-relative cart-link">
        <span class="alert-count">{{ $cartCount }}</span>
        <i class='bx bx-shopping-bag'></i>
    </a>
</li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="primary-menu">
                <nav class="navbar navbar-expand-xl w-100 navbar-dark container mb-0 p-0">
                    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar">
                      <div class="offcanvas-header">
                        <div class="offcanvas-logo"><img src="{{ App\Helpers\ImageHelper::getWebsiteLogo($siteLogoDark) }}" width="100" alt="{{ $siteName }}"></div>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                      </div>
                      <div class="offcanvas-body primary-menu">
                    <ul class="navbar-nav justify-content-start flex-grow-1 gap-1">
                      <li class="nav-item">
                        <a class="nav-link" href="{{ route('frontend.home') }}">{{ __t('Home') }}</a>
                      </li>
                      @foreach ($categories as $category)
                      <li class="nav-item dropdown mega-menu">
                        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="{{ route('frontend.products.index', ['category' => $category->slug]) }}">
                          {{ $category->name }}
                        </a>
                        @if ($category->subCategories->isNotEmpty())
                        <div class="dropdown-menu mega-menu-content">
                          <div class="container">
                            <div class="row">
                              @foreach ($category->subCategories->chunk(4) as $chunk)
                                @foreach ($chunk as $sub)
                                  <div class="col-md-3">
                                    <h6 class="mega-menu-title mb-3">
                                      <a href="{{ route('frontend.products.index', ['category' => $sub->slug]) }}" class="text-decoration-none">
                                        {{ $sub->name }}
                                        @if ($sub->childCategories->isNotEmpty())
                                          <i class='bx bx-chevron-right ms-1'></i>
                                        @endif
                                      </a>
                                    </h6>
                                    @if ($sub->childCategories->isNotEmpty())
                                    <ul class="list-unstyled mb-4">
                                      @foreach ($sub->childCategories as $child)
                                      <li class="mb-2">
                                        <a class="mega-menu-link" href="{{ route('frontend.products.index', ['category' => $child->slug]) }}">
                                          {{ $child->name }}
                                        </a>
                                      </li>
                                      @endforeach
                                    </ul>
                                    @endif
                                  </div>
                                @endforeach
                              @endforeach
                            </div>
                          </div>
                        </div>
                        @endif
                      </li>
                      @endforeach
                          <li class="nav-item">
                            <a class="nav-link" href="{{ route('frontend.about-us') }}">{{ __t('About') }}</a>
                          </li>
                          <li class="nav-item">
                            <a class="nav-link" href="{{ route('frontend.contact-us') }}">{{ __t('Contact') }}</a>
                          </li>
                          <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;" data-bs-toggle="dropdown">
                              {{ __t('Account') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('frontend.user.profile') }}">{{ __t('My Profile') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('frontend.user.orders') }}">{{ __t('My Orders') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('frontend.wishlist.index') }}">{{ __t('Wishlist') }}</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                @guest
                                <li><a class="dropdown-item" href="javascript:;" data-bs-toggle="modal" data-bs-target="#authModal" data-auth-tab="login">{{ __t('Login') }}</a></li>
                                <li><a class="dropdown-item" href="javascript:;" data-bs-toggle="modal" data-bs-target="#authModal" data-auth-tab="register">{{ __t('Register') }}</a></li>
                                @endguest
                            </ul>
                          </li>
                          <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;" data-bs-toggle="dropdown">
                              {{ __t('Blog') }}
                            </a>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="{{ route('frontend.blog-post') }}">{{ __t('Blog Post') }}</a></li>
                              <li><a class="dropdown-item" href="{{ route('frontend.blog-read') }}">{{ __t('Blog Read') }}</a></li>
                            </ul>
                          </li>
                        </ul>
                      </div>
                    </div>	
                  </nav>
            </div>
        </div>

        @yield('before-page-wrapper')

        <div class="page-wrapper">
            <div class="page-content">
                @yield('content')
            </div>
        </div>

        <footer>
            <section class="py-5 border-top bg-light">
                <div class="container">
                    <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-4">
                        <div class="col">
                            <div class="footer-section1">
                                <h5 class="mb-4 text-uppercase fw-bold">{{ __t('Contact Info') }}</h5>
                                <div class="address mb-3">
                                    <h6 class="mb-0 text-uppercase fw-bold">{{ __t('Address') }}</h6>
                                    <p class="mb-0">{{ $contactAddress ?? __('123 Street Name, City, Australia') }}</p>
                                </div>
                                <div class="phone mb-3">
                                    <h6 class="mb-0 text-uppercase fw-bold">{{ __t('Phone') }}</h6>
                                    <p class="mb-0">{{ $contactPhone ?? __('Toll Free (123) 472-796') }}</p>
                                </div>
                                <div class="email mb-3">
                                    <h6 class="mb-0 text-uppercase fw-bold">{{ __t('Email') }}</h6>
                                    <p class="mb-0">{{ $contactEmail ?? __('mail@example.com') }}</p>
                                </div>
                                <div class="working-days mb-3">
                                    <h6 class="mb-0 text-uppercase fw-bold">{{ __t('WORKING DAYS') }}</h6>
                                    <p class="mb-0">{{ __t('Mon - FRI / 9:30 AM - 6:30 PM') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="footer-section2">
                                <h5 class="mb-4 text-uppercase fw-bold">{{ __t('Categories') }}</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-1"><a href="javascript:;"><i class='bx bx-chevron-right'></i> Jeans</a></li>
                                    <li class="mb-1"><a href="javascript:;"><i class='bx bx-chevron-right'></i> T-Shirts</a></li>
                                    <li class="mb-1"><a href="javascript:;"><i class='bx bx-chevron-right'></i> Sports</a></li>
                                    <li class="mb-1"><a href="javascript:;"><i class='bx bx-chevron-right'></i> Shirts & Tops</a></li>
                                    <li class="mb-1"><a href="javascript:;"><i class='bx bx-chevron-right'></i> Clogs & Mules</a></li>
                                    <li class="mb-1"><a href="javascript:;"><i class='bx bx-chevron-right'></i> Sunglasses</a></li>
                                    <li class="mb-1"><a href="javascript:;"><i class='bx bx-chevron-right'></i> Bags & Wallets</a></li>
                                    <li class="mb-1"><a href="javascript:;"><i class='bx bx-chevron-right'></i> Sneakers & Athletic</a></li>
                                    <li class="mb-1"><a href="javascript:;"><i class='bx bx-chevron-right'></i> Electronis</a></li>
                                    <li class="mb-1"><a href="javascript:;"><i class='bx bx-chevron-right'></i> Furniture</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col">
                            <div class="footer-section3">
                                <h5 class="mb-4 text-uppercase fw-bold">{{ __t('Popular Tags') }}</h5>
                                <div class="tags-box d-flex flex-wrap gap-2">
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Cloths</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Electronis</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Furniture</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Sports</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Men Wear</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Women Wear</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Laptops</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Formal Shirts</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Topwear</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Headphones</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Bottom Wear</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Bags</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Sofa</a>
                                    <a href="javascript:;" class="btn btn-ecomm btn-outline-dark">Shoes</a>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="footer-section4">
                                <h5 class="mb-4 text-uppercase fw-bold">{{ __t('Quick Links') }}</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><a href="{{ route('frontend.become-seller') }}"><i class='bx bx-chevron-right'></i> {{ __t('Become a Seller') }}</a></li>
                                    <li class="mb-2"><a href="{{ route('frontend.blog') }}"><i class='bx bx-chevron-right'></i> {{ __t('Blog') }}</a></li>
                                    <li class="mb-2"><a href="{{ route('frontend.about-us') }}"><i class='bx bx-chevron-right'></i> {{ __t('About Us') }}</a></li>
                                    <li class="mb-2"><a href="{{ route('frontend.contact-us') }}"><i class='bx bx-chevron-right'></i> {{ __t('Contact Us') }}</a></li>
                                </ul>
                                <h5 class="mb-4 text-uppercase fw-bold mt-4">{{ __t('Stay informed') }}</h5>
                                <div class="subscribe">
                                    <input type="text" class="form-control" placeholder="Enter Your Email" />
                                    <div class="mt-3 d-grid">
                                        <a href="javascript:;" class="btn btn-dark btn-ecomm">{{ __t('Subscribe') }}</a>
                                    </div>
                                    <p class="mt-3 mb-0">{{ __t('Subscribe to our newsletter to receive early discount offers, updates and new products info.') }}</p>
                                </div>
                                <div class="download-app mt-3">
                                    <h6 class="mb-3 text-uppercase fw-bold">{{ __t('Download our app') }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="javascript:;">
                                            <img src="{{ asset('frontend/assets/images/icons/apple-store.png') }}" class="" width="140" alt="" />
                                        </a>
                                        <a href="javascript:;">
                                            <img src="{{ asset('frontend/assets/images/icons/play-store.png') }}" class="" width="140" alt="" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="footer-strip text-center py-3 border-top positon-absolute bottom-0">
                <div class="container">
                    <div class="d-flex flex-column flex-lg-row align-items-center gap-3 justify-content-between">
                        <p class="mb-0">{{ __t('Copyright © 2022. All right reserved.') }}</p>
                        <div class="payment-icon">
                            <div class="row row-cols-auto g-2 justify-content-end">
                                <div class="col">
                                    <img src="{{ asset('frontend/assets/images/icons/visa.png') }}" alt="" />
                                </div>
                                <div class="col">
                                    <img src="{{ asset('frontend/assets/images/icons/paypal.png') }}" alt="" />
                                </div>
                                <div class="col">
                                    <img src="{{ asset('frontend/assets/images/icons/mastercard.png') }}" alt="" />
                                </div>
                                <div class="col">
                                    <img src="{{ asset('frontend/assets/images/icons/american-express.png') }}" alt="" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </footer>

        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    </div>

    <!-- Login/Register Modal -->
    <div class="modal fade auth-modal" id="authModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <div class="w-100 text-center">
              <h4 class="text-white fw-bold mb-0">{{ __t('Welcome to') }} {{ $siteName }}</h4>
              <p class="text-white-50 mb-0 mt-1">{{ __t('Sign in to continue') }}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4 pt-2">
            <ul class="nav nav-pills nav-justified mb-4" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="loginTabBtn" data-bs-toggle="pill" data-bs-target="#loginTab" type="button">{{ __t('Sign In') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="registerTabBtn" data-bs-toggle="pill" data-bs-target="#registerTab" type="button">{{ __t('Register') }}</button>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="loginTab">
                <form id="loginForm">
                  @csrf
                  <div class="mb-3">
                    <label class="form-label">{{ __t('Email Address') }}</label>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{{ __t('Password') }}</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                  </div>
                  <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">{{ __t('Remember Me') }}</label>
                  </div>
                  <button type="submit" class="btn btn-dark w-100">{{ __t('Sign In') }}</button>
                  <div class="alert alert-danger mt-3 d-none" id="loginError"></div>
                  <div class="text-center mt-3">
                    <span>{{ __t("Don't have an account?") }} <a href="javascript:;" class="auth-switch-tab" data-tab="register">{{ __t('Register here') }}</a></span>
                  </div>
                </form>
              </div>
              <div class="tab-pane fade" id="registerTab">
                <form id="registerForm">
                  @csrf
                  <div class="mb-3">
                    <label class="form-label">{{ __t('Name') }}</label>
                    <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{{ __t('Email Address') }}</label>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{{ __t('Phone') }}</label>
                    <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{{ __t('Password') }}</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{{ __t('Confirm Password') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
                  </div>
                  <button type="submit" class="btn btn-dark w-100">{{ __t('Register') }}</button>
                  <div class="alert alert-danger mt-3 d-none" id="registerError"></div>
                  <div class="text-center mt-3">
                    <span>{{ __t('Already have an account?') }} <a href="javascript:;" class="auth-switch-tab" data-tab="login">{{ __t('Sign in here') }}</a></span>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/plugins/OwlCarousel/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/plugins/OwlCarousel/js/owl.carousel2.thumbs.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
    <script>
        var csrfToken = '{{ csrf_token() }}';
        var addToCartUrl = '{{ route("frontend.cart.add") }}';
        var removeFromCartUrl = '{{ route("frontend.cart.remove-by-product") }}';
        var wishlistToggleUrl = '{{ route("frontend.wishlist.toggle") }}';
        var cartCountUrl = '{{ route("frontend.cart.count") }}';
    </script>
    <script src="{{ asset('frontend/assets/js/app.js') }}"></script>
    <script>
    $(function() {
        // Switch to correct tab when modal opens
        $('#authModal').on('show.bs.modal', function(e) {
            var tab = $(e.relatedTarget).data('auth-tab');
            if (tab === 'register') {
                $('#registerTabBtn').tab('show');
            } else {
                $('#loginTabBtn').tab('show');
            }
            $('#loginError, #registerError').addClass('d-none');
            $('#loginForm, #registerForm')[0].reset();
        });

        // Switch tabs via links inside forms
        $('.auth-switch-tab').on('click', function() {
            var tab = $(this).data('tab');
            if (tab === 'register') {
                $('#registerTabBtn').tab('show');
            } else {
                $('#loginTabBtn').tab('show');
            }
            $('#loginError, #registerError').addClass('d-none');
        });

        // Login form submission
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $error = $('#loginError');
            $error.addClass('d-none');
            $form.find('button[type="submit"]').prop('disabled', true).html('Signing in...');
            $.ajax({
                url: '{{ route("frontend.login") }}',
                method: 'POST',
                data: $form.serialize(),
                success: function(res) {
                    window.location.href = res.redirect || '{{ route("frontend.home") }}';
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors || {};
                    var msg = Object.values(errors).flat().join('<br>') || 'Invalid email or password';
                    $error.html(msg).removeClass('d-none');
                },
                complete: function() {
                    $form.find('button[type="submit"]').prop('disabled', false).html('Sign In');
                }
            });
        });

        // Register form submission
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $error = $('#registerError');
            $error.addClass('d-none');
            $form.find('button[type="submit"]').prop('disabled', true).html('Registering...');
            $.ajax({
                url: '{{ route("frontend.register") }}',
                method: 'POST',
                data: $form.serialize(),
                success: function(res) {
                    window.location.href = res.redirect || '{{ route("frontend.home") }}';
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors || {};
                    var msg = Object.values(errors).flat().join('<br>') || 'Registration failed';
                    $error.html(msg).removeClass('d-none');
                },
                complete: function() {
                    $form.find('button[type="submit"]').prop('disabled', false).html('Register');
                }
            });
        });
    });
    </script>
    <script>
    $(function() {
        var searchUrl = '{{ route("frontend.products.search") }}';
        var shopUrl = '{{ route("frontend.products.index") }}';
        var debounceTimer;
        var $input = $('#headerSearchInput');
        var $results = $('#searchResults');

        function getResultUrl(item) {
            switch (item.type) {
                case 'product': return '{{ url("product") }}/' + item.slug;
                case 'category': return shopUrl + '?category=' + item.slug;
                case 'subcategory': return shopUrl + '?subcategory=' + item.slug;
                case 'child_category': return shopUrl + '?child_category=' + item.slug;
                default: return '#';
            }
        }

        function typeLabel(type) {
            var labels = { product: 'Products', category: 'Categories', subcategory: 'Subcategories', child_category: 'Subcategories' };
            return labels[type] || '';
        }

        function renderResults(data) {
            $results.empty().removeClass('d-none');

            if (!data || data.length === 0) {
                $results.append('<div class="search-result-item" style="cursor:default;color:#6c757d;font-size:13px;justify-content:center">No results found</div>');
                return;
            }

            var grouped = {};
            $.each(data, function(i, item) {
                if (!grouped[item.type]) grouped[item.type] = [];
                grouped[item.type].push(item);
            });

            var order = ['product', 'category', 'subcategory', 'child_category'];
            $.each(order, function(t, type) {
                var items = grouped[type];
                if (!items || !items.length) return;

                $results.append('<div class="search-result-section">' + typeLabel(type) + '</div>');

                $.each(items, function(i, item) {
                    var imgHtml = item.image ? '<img src="' + item.image + '" alt="">' : '';
                    var priceHtml = item.price ? '<span class="result-price">' + item.price + '</span>' : '';
                    var url = getResultUrl(item);
                    $results.append(
                        '<a href="' + url + '" class="search-result-item">' +
                            imgHtml +
                            '<div class="result-info">' +
                                '<div class="result-name">' + $('<span>').text(item.name).html() + '</div>' +
                                (item.type !== 'product' ? '<div class="result-meta">' + typeLabel(item.type).slice(0,-1) + '</div>' : '') +
                            '</div>' +
                            priceHtml +
                        '</a>'
                    );
                });
            });
        }

        $input.on('input', function() {
            var q = $(this).val().trim();
            if (q.length < 2) {
                $results.addClass('d-none');
                return;
            }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                $.getJSON(searchUrl, { q: q }, function(res) {
                    if (res.status && res.data) {
                        renderResults(res.data);
                    }
                }).fail(function() {
                    $results.addClass('d-none');
                });
            }, 2000);
        });

        // Form submit: redirect to product page if exact name match
        $('form').has($input).on('submit', function(e) {
            e.preventDefault();
            var q = $input.val().trim();
            if (q.length < 2) { $input.closest('form')[0].submit(); return; }

            $.getJSON(searchUrl, { q: q }, function(res) {
                if (res.status && res.data) {
                    var products = res.data.filter(function(item) { return item.type === 'product'; });
                    var exact = products.filter(function(item) { return item.name.toLowerCase() === q.toLowerCase(); });
                    if (exact.length === 1) {
                        window.location.href = getResultUrl(exact[0]);
                        return;
                    }
                }
                $input.closest('form')[0].submit();
            }).fail(function() {
                $input.closest('form')[0].submit();
            });
        });

        // Hide dropdown on focus loss
        $input.on('blur', function() {
            setTimeout(function() { $results.addClass('d-none'); }, 200);
        });
        $input.on('focus', function() {
            if ($results.children().length) $results.removeClass('d-none');
        });
    });
    </script>
    {!! $__env->yieldPushContent('scripts') !!}
</body>
</html>
