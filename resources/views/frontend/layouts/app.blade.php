@php
    use App\Models\Category;
    use App\Models\Cart;
    $categories = Category::where('is_active', 1)
        ->with(['subCategories' => function ($q) {
            $q->where('is_active', 1)->with('childCategories');
        }])
        ->orderBy('name')
        ->get();

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
    @stack('styles')
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
                                        <div><i class="flag-icon flag-icon-{{ strtolower(session('country_code', 'US')) }}"></i></div>
                                        <div><span>{{ strtoupper(session('currency_code', 'USD')) }}</span></div>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-lg-end">
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'US') }}"><i class="flag-icon flag-icon-us me-2"></i><span>{{ __t('USA (USD)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'GB') }}"><i class="flag-icon flag-icon-gb me-2"></i><span>{{ __t('UK (GBP)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'IN') }}"><i class="flag-icon flag-icon-in me-2"></i><span>{{ __t('India (INR)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'CN') }}"><i class="flag-icon flag-icon-cn me-2"></i><span>{{ __t('China (CNY)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'JP') }}"><i class="flag-icon flag-icon-jp me-2"></i><span>{{ __t('Japan (JPY)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'KR') }}"><i class="flag-icon flag-icon-kr me-2"></i><span>{{ __t('South Korea (KRW)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'DE') }}"><i class="flag-icon flag-icon-de me-2"></i><span>{{ __t('Germany (EUR)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'FR') }}"><i class="flag-icon flag-icon-fr me-2"></i><span>{{ __t('France (EUR)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'AE') }}"><i class="flag-icon flag-icon-ae me-2"></i><span>{{ __t('UAE (AED)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'AU') }}"><i class="flag-icon flag-icon-au me-2"></i><span>{{ __t('Australia (AUD)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'SG') }}"><i class="flag-icon flag-icon-sg me-2"></i><span>{{ __t('Singapore (SGD)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'MY') }}"><i class="flag-icon flag-icon-my me-2"></i><span>{{ __t('Malaysia (MYR)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'TH') }}"><i class="flag-icon flag-icon-th me-2"></i><span>{{ __t('Thailand (THB)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'BR') }}"><i class="flag-icon flag-icon-br me-2"></i><span>{{ __t('Brazil (BRL)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'RU') }}"><i class="flag-icon flag-icon-ru me-2"></i><span>{{ __t('Russia (RUB)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'SA') }}"><i class="flag-icon flag-icon-sa me-2"></i><span>{{ __t('Saudi Arabia (SAR)') }}</span></a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'BD') }}"><i class="flag-icon flag-icon-bd me-2"></i><span>{{ __t('Bangladesh (BDT)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'PK') }}"><i class="flag-icon flag-icon-pk me-2"></i><span>{{ __t('Pakistan (PKR)') }}</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('change.country', 'LK') }}"><i class="flag-icon flag-icon-lk me-2"></i><span>{{ __t('Sri Lanka (LKR)') }}</span></a>
                                </div>
                            </li>
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
                        <div class="col-12 col-xl order-4 order-xl-0">
                            <div class="input-group flex-nowrap pb-3 pb-xl-0">
                                <input type="text" class="form-control w-100 border-dark border border-3" placeholder="Search for Products">
                                <button class="btn btn-dark btn-ecomm border-3" type="button">{{ __t('Search') }}</button>
                            </div>
                        </div>
                        <div class="col-auto d-none d-xl-flex">
                            <div class="d-flex align-items-center gap-3">
                                <div class="fs-1 text-content"><i class='bx bx-headphone'></i></div>
                                <div class="">
                                    <p class="mb-0 text-content">{{ __t('CALL US NOW') }}</p>
                                    <h5 class="mb-0">+011 5827918</h5>
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
                                        <li class="nav-item"><a href="{{ route('frontend.wishlist.index') }}" class="nav-link cart-link"><i class='bx bx-heart'></i></a></li>
                                        <li class="nav-item dropdown dropdown-large">
                                            <a href="#" class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative cart-link" data-bs-toggle="dropdown">
                                                <span class="alert-count">{{ $cartCount }}</span>
                                                <i class='bx bx-shopping-bag'></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ route('frontend.cart.index') }}">
                                                    <div class="cart-header">
                                                        <p class="cart-header-title mb-0">{{ $cartCount }} {{ __t('ITEMS') }}</p>
                                                        <p class="cart-header-clear ms-auto mb-0">{{ __t('VIEW CART') }}</p>
                                                    </div>
                                                </a>
                                                <div class="cart-list">
                                                    @forelse ($headerCartItems->take(5) as $ci)
                                                    @php
                                                        $ciImg = $ci->image ? App\Helpers\ImageHelper::getProductImage($ci->image) : asset('frontend/assets/images/products/01.png');
                                                        $ciName = $ci->product->name ?? 'Product';
                                                        $ciPrice = $ci->variant->price ?? $ci->price ?? 0;
                                                    @endphp
                                                    <a class="dropdown-item" href="{{ route('frontend.products.show', $ci->product->slug ?? $ci->product_id) }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1">
                                                                <h6 class="cart-product-title">{{ $ciName }}</h6>
                                                                <p class="cart-product-price">{{ $ci->qty }} X {{ App\Helpers\PriceHelper::formatPrice($ciPrice) }}</p>
                                                            </div>
                                                            <div class="position-relative">
                                                                <a href="javascript:;" class="cart-product-cancel position-absolute mini-cart-remove" data-cart-id="{{ $ci->id }}"><i class='bx bx-x'></i></a>
                                                                <div class="cart-product">
                                                                    <img src="{{ $ciImg }}" class="" alt="{{ $ciName }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    @empty
                                                    <div class="dropdown-item text-center py-3 text-muted">{{ __t('Your cart is empty') }}</div>
                                                    @endforelse
                                                </div>
                                                @if ($headerCartItems->count() > 0)
                                                <a href="{{ route('frontend.cart.index') }}">
                                                    <div class="cart-footer text-center pt-3 pb-3 border-top">
                                                        <h6 class="mb-0">{{ __t('Total:') }} {{ App\Helpers\PriceHelper::formatPrice($cartTotalPrice) }}</h6>
                                                    </div>
                                                </a>
                                                @endif
                                            </div>
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
                                    <p class="mb-0">{{ __t('123 Street Name, City, Australia') }}</p>
                                </div>
                                <div class="phone mb-3">
                                    <h6 class="mb-0 text-uppercase fw-bold">{{ __t('Phone') }}</h6>
                                    <p class="mb-0">{{ __t('Toll Free (123) 472-796') }}</p>
                                    <p class="mb-0">{{ __t('Mobile : +91-9910XXXX') }}</p>
                                </div>
                                <div class="email mb-3">
                                    <h6 class="mb-0 text-uppercase fw-bold">{{ __t('Email') }}</h6>
                                    <p class="mb-0">{{ __t('mail@example.com') }}</p>
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
    @stack('scripts')
</body>
</html>
