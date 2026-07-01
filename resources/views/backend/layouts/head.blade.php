<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(app()->getLocale() == 'ar') dir="rtl" @endif data-bs-theme="light" data-topbar-color="light" data-layout-width="fluid">

<head>
     <!-- Title Meta -->
     <meta charset="utf-8">
     <meta name="csrf-token" content="{{ csrf_token() }}">
     <title>{{ $siteName }} | Admin Dashboard </title>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta name="description" content="A fully responsive premium admin dashboard template" />
     <meta name="author" content="Techzaa" />
     <meta http-equiv="X-UA-Compatible" content="IE=edge" />

     <!-- Google Fonts -->
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

     <!-- App favicon -->
     <link rel="shortcut icon" href="{{ $siteFavicon ? asset('uploads/settings/'.$siteFavicon) : asset('backend/assets/images/favicon.ico') }}">

     <!-- Vendor css (Require in all Page) -->
     <link href="{{ asset('backend/assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />

     <!-- Icons css (Require in all Page) -->
     <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

     <!-- App css (Require in all Page) -->
     <link href="{{ asset('backend/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

     <!-- DateRangePicker css -->
     <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

     <!-- Custom css -->
     <link href="{{ asset('backend/assets/css/custom.css') }}" rel="stylesheet" type="text/css" />

     <!-- Toastr css -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

     <!-- ApexCharts -->
     <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

     <!-- Theme Config js (Require in all Page) -->
     <script>
          var BaseUrl = "{{ url('/') }}";
     </script>
     <script src="{{ asset('backend/assets/js/config.js') }}"></script>

     <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>


</head>

<body>


     <!-- START Wrapper -->
     <div class="wrapper">
          <!-- ========== Topbar Start ========== -->
          <header class="topbar mb-4">
               <div class="container-fluid">
                    <div class="navbar-header">
                         <div class="d-flex align-items-center">
                              <!-- Menu Toggle Button -->
                              <div class="topbar-item">
                                   <button type="button" class="button-toggle-menu me-2">
                                        <iconify-icon icon="solar:hamburger-menu-linear" class="fs-24 align-middle"></iconify-icon>
                                   </button>
                              </div>

                              <!-- Menu Toggle Button -->
                              <!-- <div class="topbar-item">
                                   <h4 class="fw-bold topbar-button pe-none text-uppercase mb-0">Categories List</h4>
                              </div> -->
                         </div>

                         <div class="d-flex align-items-center gap-1">
                              <div class="topbar-item">


                              </div>
                              @if(auth()->check() && (string)auth()->user()->role === '2')
                              <div class="topbar-item d-none d-md-flex">
                                   <a href="{{ route('vendor.wallet') }}" class="topbar-button d-inline-flex align-items-center">
                                        <iconify-icon icon="solar:wallet-2-linear" class="fs-22 align-middle me-1"></iconify-icon>
                                        <span class="fw-semibold">Wallet: {{ number_format(auth()->user()->wallet_balance ?? 0, 2) }}</span>
                                   </a>
                              </div>
                              @endif
                              <!-- Theme Color (Light/Dark) -->
                              <div class="topbar-item">
                                   <button type="button" class="topbar-button" id="light-dark-mode">
                                        <iconify-icon icon="solar:moon-linear" class="fs-24 align-middle"></iconify-icon>
                                   </button>
                              </div>

                              <!-- Notification -->
                              <div class="dropdown topbar-item">
                                   <button type="button" class="topbar-button position-relative" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <iconify-icon icon="solar:bell-bing-linear" class="fs-24 align-middle"></iconify-icon>
                                        @php $unreadCount = Auth::user()->unreadNotifications->count(); @endphp
                                        @if($unreadCount > 0)
                                        <span class="position-absolute topbar-badge fs-10 translate-middle badge bg-danger rounded-pill">{{ $unreadCount }}<span class="visually-hidden">unread messages</span></span>
                                        @endif
                                   </button>
                                   <div class="dropdown-menu py-0 dropdown-lg dropdown-menu-end" aria-labelledby="page-header-notifications-dropdown">
                                        <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                             <div class="row align-items-center">
                                                  <div class="col">
                                                       <h6 class="m-0 fs-16 fw-semibold"> Notifications</h6>
                                                  </div>
                                                  <div class="col-auto">
                                                       <a href="{{ route('notifications.markAllRead') }}" class="text-dark text-decoration-underline">
                                                            <small>Clear All</small>
                                                       </a>
                                                  </div>
                                             </div>
                                        </div>
                                        <div data-simplebar style="max-height: 280px;">
                                        @forelse(Auth::user()->notifications()->whereNull('read_at')->latest()->take(10)->get() as $notification)
                                             <!-- Item -->
                                             <a href="{{ route('notifications.markAsRead', $notification->id) }}" class="dropdown-item py-3 border-bottom text-wrap {{ $notification->read_at ? '' : 'bg-light' }}">
                                                  <div class="d-flex">
                                                       <div class="flex-shrink-0">
                                                            <div class="avatar-sm me-2">
                                                                 <span class="avatar-title bg-soft-{{ ($notification->data['priority'] ?? 'low') == 'critical' ? 'danger' : (($notification->data['priority'] ?? 'low') == 'medium' ? 'warning' : 'info') }} text-{{ ($notification->data['priority'] ?? 'low') == 'critical' ? 'danger' : (($notification->data['priority'] ?? 'low') == 'medium' ? 'warning' : 'info') }} fs-20 rounded-circle">
                                                                      <iconify-icon icon="{{ $notification->data['icon'] ?? 'solar:bell-linear' }}"></iconify-icon>
                                                                 </span>
                                                            </div>
                                                       </div>
                                                       <div class="flex-grow-1">
                                                            <p class="mb-0 fw-semibold">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                                            <p class="mb-0 text-wrap">{!! $notification->data['message'] ?? '' !!}</p>
                                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                       </div>
                                                  </div>
                                             </a>
                                             @empty
                                             <div class="p-3 text-center">
                                                  <p class="mb-0 text-muted">No notifications found</p>
                                             </div>
                                             @endforelse
                                        </div>
                                        <div class="text-center py-3">
                                             <a href="{{ route('notifications.index') }}" class="btn btn-primary btn-sm">View All Notification <iconify-icon icon="solar:arrow-right-linear" class="ms-1 align-middle"></iconify-icon></a>
                                        </div>
                                   </div>
                              </div>

                              <!-- Theme Setting -->
                              <!-- <div class="topbar-item d-none d-md-flex">
                                   <button type="button" class="topbar-button" id="theme-settings-btn" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
                                        <iconify-icon icon="solar:settings-linear" class="fs-24 align-middle"></iconify-icon>
                                   </button>
                              </div> -->

                              <!-- Language Dropdown -->
                              <div class="dropdown topbar-item d-none d-md-flex">
                                   <button type="button" class="topbar-button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <iconify-icon icon="mdi:google-translate" class="fs-24 align-middle"></iconify-icon>
                                   </button>
                                   <div class="dropdown-menu dropdown-menu-end">
                                        <a href="{{ url('change-language/en') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-united-kingdom" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">English</span>
                                        </a>
                                        <a href="{{ url('change-language/ar') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-saudi-arabia" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">Arabic</span>
                                        </a>
                                        <a href="{{ url('change-language/zh') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-china" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">Chinese</span>
                                        </a>
                                        <a href="{{ url('change-language/ja') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-japan" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">Japanese</span>
                                        </a>
                                        <a href="{{ url('change-language/hi') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-india" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">Hindi</span>
                                        </a>
                                        <a href="{{ url('change-language/de') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-germany" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">German</span>
                                        </a>
                                        <a href="{{ url('change-language/fr') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-france" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">French</span>
                                        </a>
                                        <a href="{{ url('change-language/ko') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-south-korea" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">Korean</span>
                                        </a>
                                        <a href="{{ url('change-language/pt') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-brazil" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">Portuguese</span>
                                        </a>
                                        <a href="{{ url('change-language/es') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-spain" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">Spanish</span>
                                        </a>
                                        <a href="{{ url('change-language/ru') }}" class="dropdown-item">
                                             <iconify-icon icon="twemoji:flag-russia" class="align-middle me-2"></iconify-icon>
                                             <span class="align-middle">Russian</span>
                                        </a>
                                   </div>
                              </div>

                              <!-- Activity -->
                              <!-- <div class="topbar-item d-none d-md-flex">
                                   <button type="button" class="topbar-button" id="theme-settings-btn" data-bs-toggle="offcanvas" data-bs-target="#theme-activity-offcanvas" aria-controls="theme-settings-offcanvas">
                                        <iconify-icon icon="solar:clock-circle-linear" class="fs-24 align-middle"></iconify-icon>
                                   </button>
                              </div> -->

                              <!-- User -->
                              <div class="dropdown topbar-item">
                                   <a type="button" class="topbar-button" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="d-flex align-items-center">

                                             @if(!empty(Auth::user()->role == '1'))
                                             @if(!empty(Auth::user()->image))
                                             <img class="rounded-circle" width="32" src="{{ \App\Helpers\ImageHelper::getProfileImage(Auth::user()->image) }}" alt="avatar-3">
                                             @else
                                             <img class="rounded-circle" width="32" src="{{ asset('backend/assets/images/users/avatar-1.jpg') }}" alt="avatar-3">
                                             @endif
                                             @elseif(Auth::user()->role == '2')
                                             @if(Auth::user()->image != null)
                                             <img class="rounded-circle" width="32" src="{{ \App\Helpers\ImageHelper::getVendorsImage(Auth::user()->image) }}" alt="avatar-3">
                                             @else
                                             <img class="rounded-circle" width="32" src="{{ asset('backend/assets/images/users/avatar-2.jpg') }}" alt="avatar-3">
                                             @endif
                                             @endif
                                        </span>
                                   </a>
                                   <div class="dropdown-menu dropdown-menu-end">
                                        <!-- item-->
                                        <h6 class="dropdown-header">{{ Auth::user()->name }}!</h6>

                                        @if(Auth::user()->role == '1')
                                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                             <iconify-icon icon="solar:user-circle-linear" class="text-muted fs-18 align-middle me-1"></iconify-icon><span class="align-middle">Profile</span>
                                        </a>
                                        @elseif(Auth::user()->role == '2')
                                        <a class="dropdown-item" href="{{ route('vendor.profile') }}">
                                             <iconify-icon icon="solar:user-circle-linear" class="text-muted fs-18 align-middle me-1"></iconify-icon><span class="align-middle">Profile</span>
                                        </a>
                                        @endif

 @if(Auth::user()->role == '1')
                                        <a class="dropdown-item" href="{{ route('admin.change.password') }}">
                                             <iconify-icon icon="solar:lock-password-linear" class="text-muted fs-18 align-middle me-1"></iconify-icon><span class="align-middle">Change Password</span>
                                        </a>
                                        @elseif(Auth::user()->role == '2')

                                        
                                        <a class="dropdown-item" href="{{ route('vendor.change.password') }}">
                                             <iconify-icon icon="solar:lock-password-linear" class="text-muted fs-18 align-middle me-1"></iconify-icon><span class="align-middle">Change Password</span>
                                        </a>
                                        @endif


                                        <div class="dropdown-divider my-1"></div>

                                        <a class="dropdown-item text-danger" href="{{route('logout')}}">
                                             <iconify-icon icon="solar:logout-3-linear" class="fs-18 align-middle me-1"></iconify-icon><span class="align-middle">Logout</span>
                                        </a>

                                        <!-- @if(Auth::user()->role == '1')
                                         <div class="dropdown-divider my-1"></div>
                                         <a class="dropdown-item" href="{{ route('download.handover.pdf') }}">
                                              <iconify-icon icon="solar:document-text-linear" class="text-primary fs-18 align-middle me-1"></iconify-icon><span class="align-middle">Handover Doc</span>
                                         </a>
                                         @endif -->
                                   </div>
                              </div>
                         </div>
                    </div>
               </div>
          </header>

          <!-- Activity Timeline -->
          <div>
               <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-activity-offcanvas" style="max-width: 450px; width: 100%;">
                    <div class="d-flex align-items-center bg-primary p-3 offcanvas-header">
                         <h5 class="text-white m-0 fw-semibold">Activity Stream</h5>
                         <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>

                    <div class="offcanvas-body p-0">
                         <div data-simplebar class="h-100 p-4">
                              <div class="position-relative ms-2">
                                   <span class="position-absolute start-0  top-0 border border-dashed h-100"></span>
                                   <div class="position-relative ps-4">
                                        <div class="mb-4">
                                             <span class="position-absolute start-0 avatar-sm translate-middle-x bg-danger d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-20"><iconify-icon icon="iconamoon:folder-check-duotone"></iconify-icon></span>
                                             <div class="ms-2">
                                                  <h5 class="mb-1 text-dark fw-semibold fs-15 lh-base">Report-Fix / Update </h5>
                                                  <p class="d-flex align-items-center">Add 3 files to <span class=" d-flex align-items-center text-primary ms-1"><iconify-icon icon="iconamoon:file-light"></iconify-icon> Tasks</span></p>
                                                  <div class="bg-light bg-opacity-50 rounded-2 p-2">
                                                       <div class="row">
                                                            <div class="col-lg-6 border-end border-light">
                                                                 <div class="d-flex align-items-center gap-2">
                                                                      <i class="bx bxl-figma fs-20 text-red"></i>
                                                                      <a href="#!" class="text-dark fw-medium">Concept.fig</a>
                                                                 </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                 <div class="d-flex align-items-center gap-2">
                                                                      <i class="bx bxl-file-doc fs-20 text-success"></i>
                                                                      <a href="#!" class="text-dark fw-medium">larkon.docs</a>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                  </div>
                                                  <h6 class="mt-2 text-muted">Monday , 4:24 PM</h6>
                                             </div>
                                        </div>
                                   </div>
                                   <div class="position-relative ps-4">
                                        <div class="mb-4">
                                             <span class="position-absolute start-0 avatar-sm translate-middle-x bg-success d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-20"><iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon></span>
                                             <div class="ms-2">
                                                  <h5 class="mb-1 text-dark fw-semibold fs-15 lh-base">Project Status
                                                  </h5>
                                                  <p class="d-flex align-items-center mb-0">Marked<span class=" d-flex align-items-center text-primary mx-1"><iconify-icon icon="iconamoon:file-light"></iconify-icon> Design </span> as <span class="badge bg-success-subtle text-success px-2 py-1 ms-1"> Completed</span></p>
                                                  <div class="d-flex align-items-center gap-3 mt-1 bg-light bg-opacity-50 p-2 rounded-2">
                                                       <a href="#!" class="fw-medium text-dark">UI/UX Figma Design</a>
                                                       <div class="ms-auto">
                                                            <a href="#!" class="fw-medium text-primary fs-18" data-bs-toggle="tooltip" data-bs-title="Download" data-bs-placement="bottom"><iconify-icon icon="iconamoon:cloud-download-duotone"></iconify-icon></a>
                                                       </div>
                                                  </div>
                                                  <h6 class="mt-3 text-muted">Monday , 3:00 PM</h6>
                                             </div>
                                        </div>
                                   </div>
                                   <div class="position-relative ps-4">
                                        <div class="mb-4">
                                             <span class="position-absolute start-0 avatar-sm translate-middle-x bg-primary d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-16">UI</span>
                                             <div class="ms-2">
                                                  <h5 class="mb-1 text-dark fw-semibold fs-15">Larkon Application UI v2.0.0 <span class="badge bg-primary-subtle text-primary px-2 py-1 ms-1"> Latest</span>
                                                  </h5>
                                                  <p>Get access to over 20+ pages including a dashboard layout, charts, kanban board, calendar, and pre-order E-commerce & Marketing pages.</p>
                                                  <div class="mt-2">
                                                       <a href="#!" class="btn btn-light btn-sm">Download Zip</a>
                                                  </div>
                                                  <h6 class="mt-3 text-muted">Monday , 2:10 PM</h6>
                                             </div>
                                        </div>
                                   </div>
                                   <div class="position-relative ps-4">
                                        <div class="mb-4">
                                             <span class="position-absolute start-0 translate-middle-x bg-success bg-gradient d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-20"><img src="{{ asset('backend/assets/images/users/avatar-7.jpg') }}" alt="avatar-5" class="avatar-sm rounded-circle"></span>
                                             <div class="ms-2">
                                                  <h5 class="mb-0 text-dark fw-semibold fs-15 lh-base">Alex Smith Attached Photos
                                                  </h5>
                                                  <div class="row g-2 mt-2">
                                                       <div class="col-lg-4">
                                                            <a href="#!">
                                                                 <img src="{{ asset('backend/assets/images/small/img-6.jpg') }}" alt="" class="img-fluid rounded">
                                                            </a>
                                                       </div>
                                                       <div class="col-lg-4">
                                                            <a href="#!">
                                                                 <img src="{{ asset('backend/assets/images/small/img-3.jpg') }}" alt="" class="img-fluid rounded">
                                                            </a>
                                                       </div>
                                                       <div class="col-lg-4">
                                                            <a href="#!">
                                                                 <img src="{{ asset('backend/assets/images/small/img-4.jpg') }}" alt="" class="img-fluid rounded">
                                                            </a>
                                                       </div>
                                                  </div>
                                                  <h6 class="mt-3 text-muted">Monday 1:00 PM</h6>
                                             </div>
                                        </div>
                                   </div>
                                   <div class="position-relative ps-4">
                                        <div class="mb-4">
                                             <span class="position-absolute start-0 translate-middle-x bg-success bg-gradient d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-20"><img src="{{ asset('backend/assets/images/users/avatar-6.jpg') }}" alt="avatar-5" class="avatar-sm rounded-circle"></span>
                                             <div class="ms-2">
                                                  <h5 class="mb-0 text-dark fw-semibold fs-15 lh-base">Rebecca J. added a new team member
                                                  </h5>
                                                  <p class="d-flex align-items-center gap-1"><iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon> Added a new member to Front Dashboard</p>
                                                  <h6 class="mt-3 text-muted">Monday 10:00 AM</h6>
                                             </div>
                                        </div>
                                   </div>
                                   <div class="position-relative ps-4">
                                        <div class="mb-4">
                                             <span class="position-absolute start-0 avatar-sm translate-middle-x bg-warning d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-20"><iconify-icon icon="iconamoon:certificate-badge-duotone"></iconify-icon></span>
                                             <div class="ms-2">
                                                  <h5 class="mb-0 text-dark fw-semibold fs-15 lh-base">Achievements
                                                  </h5>
                                                  <p class="d-flex align-items-center gap-1 mt-1">Earned a <iconify-icon icon="iconamoon:certificate-badge-duotone" class="text-danger fs-20"></iconify-icon>" Best Product Award"</p>
                                                  <h6 class="mt-3 text-muted">Monday 9:30 AM</h6>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <a href="#!" class="btn btn-outline-dark w-100">View All</a>
                         </div>
                    </div>
               </div>
          </div>

        
