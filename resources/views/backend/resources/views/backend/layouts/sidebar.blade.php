<!-- ========== App Menu Start ========== -->
@if(Auth::user()->role == 1)
<div class="main-nav ">
     <!-- Sidebar Logo -->
     <div class="logo-box">
          <a href="{{route('admin.dashboard')}}" class="logo-dark">
               <img src="{{ asset('backend/assets/images/small-logo.png') }}" class="logo-sm small_logo_size" alt="logo sm">
               <img src="{{ asset('backend/assets/images/logo.png') }}" class="logo-lg " alt="logo dark">
          </a>

          <a href="{{route('admin.dashboard')}}" class="logo-light">
               <img src="{{ asset('backend/assets/images/small-logo.png') }}" class="logo-sm small_logo_size" alt="logo sm">
               <img src="{{ asset('backend/assets/images/logo.png') }}" class="logo-lg" alt="logo light">
          </a>
     </div>

     <!-- Menu Toggle Button (sm-hover) -->
     <!-- <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
          <iconify-icon icon="solar:double-alt-arrow-right-linear" class="button-sm-hover-icon"></iconify-icon>
     </button> -->

     <div class="scrollbar">
          <ul class="navbar-nav" id="navbar-nav">
               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{route('admin.dashboard')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:widget-5-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.dashboard')  }} </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['vendors.*', 'vendor.*']) ? 'active' : '' }}" href="#sidebarVendorManagement" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['vendors.*', 'vendor.*']) ? 'true' : 'false' }}" aria-controls="sidebarVendorManagement">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon>
                         </span>
                         <span class="nav-text">{{ __('messages.vendor_management') }}</span>
                    </a>
                    <div class="collapse {{ request()->routeIs(['vendors.*', 'vendor.*']) ? 'show' : '' }}" id="sidebarVendorManagement">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('vendors.list') ? 'active' : '' }}" href="{{ route('vendors.list') }}">{{ __('messages.vendor')  }} {{ __('messages.list')  }} </a>
                              </li>

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('vendor.requests') ? 'active' : '' }}" href="{{route('vendor.requests')}}">{{ __('messages.vendor')  }} {{ __('messages.request')  }} </a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('vendor.payout') && request('frequency') === 'weekly' ? 'active' : '' }}" href="{{ route('vendor.payout', ['frequency' => 'weekly']) }}"> {{ __('messages.weekly_payouts')  }} </a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('vendor.payout') && (request('frequency') === 'monthly' || !request('frequency')) ? 'active' : '' }}" href="{{ route('vendor.payout', ['frequency' => 'monthly']) }}">{{ __('messages.monthly_payouts')  }} </a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('vendor.payout') && request('frequency') === 'daily' ? 'active' : '' }}" href="{{ route('vendor.payout', ['frequency' => 'daily']) }}">{{ __('messages.daily_payouts')  }}(Testing)</a>
                              </li>
                         </ul>
                    </div>
               </li>


               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['category.*', 'subcategory.*', 'child.category.*', 'brand.*']) ? 'active' : '' }}" href="#sidebarCategory" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['category.*', 'subcategory.*', 'child.category.*', 'brand.*']) ? 'true' : 'false' }}" aria-controls="sidebarCategory">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon>
                         </span>
                         <span class="nav-text">{{ __('messages.category_and_brands') }}</span>
                    </a>
                    <div class="collapse {{ request()->routeIs(['category.*', 'subcategory.*', 'child.category.*', 'brand.*']) ? 'show' : '' }}" id="sidebarCategory">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('category.list') ? 'active' : '' }}" href="{{route('category.list')}}">{{ __('messages.category')  }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('subcategory.list') ? 'active' : '' }}" href="{{route('subcategory.list')}}">{{ __('messages.subcategory')  }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('child.category.list') ? 'active' : '' }}" href="{{route('child.category.list')}}">{{ __('messages.child_category') }}</a>
                              </li>

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('brand.list') ? 'active' : '' }}" href="{{route('brand.list')}}"> {{ __('messages.brand')  }} </a>
                              </li>


                         </ul>
                    </div>
               </li>


               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['product.*', 'find.similar.product', 'add.product', 'bulk.upload.product', 'add.product.size.category']) ? 'active' : '' }}" href="#sidebarProducts" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['product.*', 'find.similar.product', 'add.product', 'bulk.upload.product', 'add.product.size.category']) ? 'true' : 'false' }}" aria-controls="sidebarProducts">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:t-shirt-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.products') }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs(['product.*', 'find.similar.product', 'add.product', 'bulk.upload.product', 'add.product.size.category']) ? 'show' : '' }}" id="sidebarProducts">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('product.list') ? 'active' : '' }}" href="{{ route('product.list') }}">{{ __('messages.product_list') }}</a>
                              </li>

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('find.similar.product') ? 'active' : '' }}" href="{{route('find.similar.product')}}">{{ __('messages.add_similar_product') }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('add.product') ? 'active' : '' }}" href="{{ route('add.product') }}">{{ __('messages.add_single_product') }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('bulk.upload.product') ? 'active' : '' }}" href="{{route('bulk.upload.product')}}">{{ __('messages.bulk_upload_product') }}</a>
                              </li>

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('add.product.size.category') ? 'active' : '' }}" href="{{route('add.product.size.category')}}"> {{ __('messages.product_size') }} </a>
                              </li>
                         </ul>
                    </div>
               </li>
               <!-- <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('campaign.*') ? 'active' : '' }}" href="{{ route('campaign.list') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:ticket-sale-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> Campaigns </span>
                    </a>
               </li> -->

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs('campaign.*') ? 'active' : '' }}" href="#sidebarCampaigns" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('campaign.*') ? 'true' : 'false' }}" aria-controls="sidebarCampaigns">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:ticket-sale-linear"></iconify-icon>
                         </span>
                         <span class="nav-text">{{ __('messages.campaigns') }}</span>
                    </a>
                    <div class="collapse {{ request()->routeIs('campaign.*') ? 'show' : '' }}" id="sidebarCampaigns">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('campaign.list') ? 'active' : '' }}" href="{{ route('campaign.list') }}">{{ __('messages.campaign_list') }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('campaign.add') ? 'active' : '' }}" href="{{ route('campaign.add') }}">{{ __('messages.create_campaign') }}</a>
                              </li>
                         </ul>
                    </div>
               </li>


               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['new.orders', 'admin.refund.list', 'orders.*']) ? 'active' : '' }}" href="#sidebarOrders" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['new.orders', 'admin.refund.list', 'orders.*']) ? 'true' : 'false' }}" aria-controls="sidebarOrders">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:bag-smile-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.orders') }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs(['new.orders', 'admin.refund.list', 'orders.*']) ? 'show' : '' }}" id="sidebarOrders">
                         <ul class="nav sub-navbar-nav">

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('new.orders') ? 'active' : '' }}" href="{{route('new.orders')}}">{{ __('messages.list') }}</a>
                              </li>

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('admin.refund.list') ? 'active' : '' }}" href="{{ route('admin.refund.list') }}">
                                        Refund Requests
                                   </a>
                              </li>

                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:shop-2-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> POS </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['offer.*', 'add.offer', 'edit.offer']) ? 'active' : '' }}" href="#sidebarOffers" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['offer.*', 'add.offer', 'edit.offer']) ? 'true' : 'false' }}" aria-controls="sidebarOffers">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:leaf-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.offers') }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs(['offer.*', 'add.offer', 'edit.offer']) ? 'show' : '' }}" id="sidebarOffers">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('offer.list') ? 'active' : '' }}" href="{{ route('offer.list') }}">{{ __('messages.list') }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('add.offer') ? 'active' : '' }}" href="{{ route('add.offer') }}">{{ __('messages.add') }}</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['coupon.*']) ? 'active' : '' }}" href="#sidebarCoupons" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['coupon.*']) ? 'true' : 'false' }}" aria-controls="sidebarCoupons">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:ticket-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.coupons') }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs(['coupon.*']) ? 'show' : '' }}" id="sidebarCoupons">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('coupon.list') ? 'active' : '' }}" href="{{ route('coupon.list') }}">{{ __('messages.list') }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('coupon.create') ? 'active' : '' }}" href="{{ route('coupon.create') }}">{{ __('messages.add') }}</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="menu-title mt-2">{{ __('messages.cms') }}</li>
               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs('banner.*') ? 'active' : '' }}" href="#sidebarBanners" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('banner.*') ? 'true' : 'false' }}" aria-controls="sidebarBanners">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:gallery-wide-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.banner') }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs('banner.*') ? 'show' : '' }}" id="sidebarBanners">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('banner.list') ? 'active' : '' }}" href="{{route('banner.list')}}">{{ __('messages.list') }}</a>
                              </li>

                         </ul>


                    </div>
               </li>



               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}" href="#sidebarBlogs" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('admin.blog.*') ? 'true' : 'false' }}" aria-controls="sidebarBlogs">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:document-text-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.blogs') ?? 'Blogs' }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.blog.*') ? 'show' : '' }}" id="sidebarBlogs">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('admin.blog.index') ? 'active' : '' }}" href="{{ route('admin.blog.index') }}">{{ __('messages.list') }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('admin.blog.add') ? 'active' : '' }}" href="{{ route('admin.blog.add') }}">{{ __('messages.add') }}</a>
                              </li>
                         </ul>
                    </div>
               </li>



               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('privacy.policy.list') ? 'active' : '' }}" href="{{route('privacy.policy.list')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:shield-keyhole-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.privacy_policy') }} </span>
                    </a>
               </li>


               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('term.and.conditions.list') ? 'active' : '' }}" href="{{route('term.and.conditions.list')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:notes-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.terms_and_conditions') }} </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vendor.policy.list') ? 'active' : '' }}" href="{{route('vendor.policy.list')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:shield-check-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.vendor_policy') }} </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('about.us.list') ? 'active' : '' }}" href="{{route('about.us.list')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:info-circle-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.about_us') }} </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('faq.list') ? 'active' : '' }}" href="{{route('faq.list')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:question-square-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.faq') }} </span>
                    </a>
               </li>






               <li class="menu-title mt-2">{{ __('messages.report') }}</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['sales.report', 'vendor.report', 'product.report', 'kyc.report']) ? 'active' : '' }}" href="#sidebarReport" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['sales.report', 'vendor.report', 'product.report', 'kyc.report']) ? 'true' : 'false' }}" aria-controls="sidebarReport">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:chart-2-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.reports') }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs(['sales.report', 'vendor.report', 'product.report', 'kyc.report']) ? 'show' : '' }}" id="sidebarReport">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('sales.report') ? 'active' : '' }}" href="{{route('sales.report')}}">{{ __('messages.sales_report') }}</a>
                                   <a class="sub-nav-link {{ request()->routeIs('vendor.report') ? 'active' : '' }}" href="{{route('vendor.report')}}">{{ __('messages.vendor_report') }}</a>
                                   <a class="sub-nav-link {{ request()->routeIs('product.report') ? 'active' : '' }}" href="{{route('product.report')}}">{{ __('messages.product_report') }}</a>
                                   <a class="sub-nav-link {{ request()->routeIs('kyc.report') ? 'active' : '' }}" href="{{route('kyc.report')}}">{{ __('messages.kyc_verification') }}</a>
                              </li>
                              <!-- <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="Report-add.html">Add</a>
                              </li> -->
                         </ul>
                    </div>
               </li>




               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('global.fees') ? 'active' : '' }}" href="{{ route('global.fees') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:money-bag-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.global_fees') }} </span>
                    </a>
               </li>
               <li class="menu-title mt-2">{{ __('messages.support') }}</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="#sidebarSupport" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('tickets.*') ? 'true' : 'false' }}" aria-controls="sidebarSupport">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:chat-square-call-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.support') }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs('tickets.*') ? 'show' : '' }}" id="sidebarSupport">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->input('type') == 'vendor' ? 'active' : '' }}" href="{{ route('tickets.index', ['type' => 'vendor']) }}">Vendor Tickets</a>
                              </li>
                              <!-- <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->input('type') == 'customer' ? 'active' : '' }}" href="{{ route('tickets.index', ['type' => 'customer']) }}">Customer Tickets</a>
                              </li> -->
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->input('type') == 'escalated' ? 'active' : '' }}" href="{{ route('tickets.index', ['type' => 'escalated']) }}">Customer Tickets</a>
                              </li>
                         </ul>
                    </div>
               </li>


               <li class="menu-title mt-2">{{ __('messages.other') }}</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['general.setting', 'payment.getway.setting', 'email.setting', 'sms.setting']) ? 'active' : '' }}" href="#sidebarWebsiteSettings" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['general.setting', 'payment.getway.setting', 'email.setting', 'sms.setting']) ? 'true' : 'false' }}" aria-controls="sidebarWebsiteSettings">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:settings-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.website_settings') ?? 'Website Settings' }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs(['general.setting', 'payment.getway.setting', 'email.setting', 'sms.setting']) ? 'show' : '' }}" id="sidebarWebsiteSettings">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('general.setting') ? 'active' : '' }}" href="{{route('general.setting')}}">{{ __('messages.general_settings') ?? 'General Settings' }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('global.fees') ? 'active' : '' }}" href="{{route('global.fees')}}">{{ __('messages.global_fees') }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('payment.getway.setting') ? 'active' : '' }}" href="{{route('payment.getway.setting')}}">{{ __('messages.payment_gateway_setting') }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('email.setting') ? 'active' : '' }}" href="{{route('email.setting')}}">{{ __('messages.email_setting') }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('sms.setting') ? 'active' : '' }}" href="{{route('sms.setting')}}">{{ __('messages.sms_settings') ?? 'SMS Settings' }}</a>
                              </li>
                         </ul>
                    </div>
               </li>


               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}" href="{{route('notifications.index')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:bell-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.notifications') }} </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="{{route('logout')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:logout-linear"></iconify-icon>
                         </span>
                         <span class="nav-text">{{ __('messages.logout') }}</span>
                    </a>
               </li>

          </ul>
     </div>
</div>

@elseif(Auth::user()->role == 2)

<div class="main-nav vendor-sidebar">
     <!-- Sidebar Logo -->
     <div class="logo-box">
          <a href="{{route('vendor.dashboard')}}" class="logo-dark">
               <img src="{{ asset('backend/assets/images/logo.png') }}" class="logo-sm" alt="logo sm">
               <img src="{{ asset('backend/assets/images/logo.png') }}" class="logo-lg" alt="logo dark">
          </a>

          <a href="{{route('vendor.dashboard')}}" class="logo-light">
               <img src="{{ asset('backend/assets/images/logo.png') }}" class="logo-sm" alt="logo sm">
               <img src="{{ asset('backend/assets/images/logo.png') }}" class="logo-lg" alt="logo light">
          </a>
     </div>

     <div class="vendor-profile-section p-3 mb-2">
          <div class="d-flex align-items-center gap-3">
               <div class="vendor-avatar">
                  
                    @if(Auth::user()->image)
              
                    <img src="{{ \App\Helpers\ImageHelper::getVendorsImage(Auth::user()->image) }}" alt="vendor logo" class="rounded-circle border border-2 border-white-50" width="48" height="48" style="object-fit: cover;">
                    @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white text-primary fw-bold border border-2 border-white-50" style="width: 48px; height: 48px; font-size: 20px;">
                         {{ strtoupper(substr(Auth::user()->store_name ?? Auth::user()->name, 0, 1)) }}
                    </div>
                    @endif
               </div>
               <div class="vendor-info">
                    <h6 class="mb-1 text-white fw-bold fs-15 text-truncate" style="max-width: 150px;">{{ Auth::user()->store_name ?? 'My Store' }}</h6>
                    @if(Auth::user()->isDocumentsVerified())
                    <div class="d-inline-flex align-items-center gap-1 bg-success-subtle text-success px-2 py-0.5 rounded-pill border border-success-subtle" style="font-size: 11px;">
                         <span class="rounded-circle bg-success" style="width: 6px; height: 6px;"></span>
                         Verified
                    </div>
                    @else
                    <div class="d-inline-flex align-items-center gap-1 bg-warning-subtle text-warning px-2 py-0.5 rounded-pill border border-warning-subtle" style="font-size: 11px;">
                         <span class="rounded-circle bg-warning" style="width: 6px; height: 6px;"></span>
                         Pending
                    </div>
                    @endif
                    <div class="mt-2 text-white fw-semibold" style="font-size: 13px;">
                         Wallet: {{ number_format(Auth::user()->wallet_balance ?? 0, 2) }}
                    </div>
               </div>
          </div>
     </div>



     <div class="scrollbar">
          <ul class="navbar-nav" id="navbar-nav">


               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}" href="{{route('vendor.dashboard')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:widget-5-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.dashboard') }} </span>
                    </a>
               </li>



               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vendor.profile') ? 'active' : '' }}" href="{{route('vendor.profile')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:user-linear"></iconify-icon>
                         </span>
                         <span class="nav-text">{{ __('messages.my_profile') }} </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vendor.kyc.documents') ? 'active' : '' }}" href="{{route('vendor.kyc.documents')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:shield-check-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.kyc_documents') }} </span>
                    </a>
               </li>

               @if(Auth::user()->isDocumentsVerified())

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vendor.campaigns') ? 'active' : '' }}" href="{{ route('vendor.campaigns') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:ticket-sale-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.campaigns') }} </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vendor.wallet') ? 'active' : '' }}" href="{{ route('vendor.wallet') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:wallet-2-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.wallet') }} </span>
                    </a>
               </li>
               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs(['vendor.payouts','vendor.payouts.show']) ? 'active' : '' }}" href="{{ route('vendor.payouts') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:hand-money-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.payouts') }} </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['product.*', 'find.similar.product', 'add.product', 'bulk.upload.product', 'add.product.size.category']) ? 'active' : '' }}" href="#sidebarProducts" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['product.*', 'find.similar.product', 'add.product', 'bulk.upload.product', 'add.product.size.category']) ? 'true' : 'false' }}" aria-controls="sidebarProducts">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:t-shirt-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.product_management') }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs(['product.*', 'find.similar.product', 'add.product', 'bulk.upload.product', 'add.product.size.category']) ? 'show' : '' }}" id="sidebarProducts">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('product.list') ? 'active' : '' }}" href="{{ route('product.list') }}"> {{ __('messages.product_list') }} </a>
                              </li>

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('find.similar.product') ? 'active' : '' }}" href="{{route('find.similar.product')}}"> {{ __('messages.find_similar_product') }} </a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('add.product') ? 'active' : '' }}" href="{{ route('add.product') }}"> {{ __('messages.add_single_product') }} </a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('bulk.upload.product') ? 'active' : '' }}" href="{{route('bulk.upload.product')}}"> {{ __('messages.bulk_upload_product') }} </a>
                              </li>


                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['new.orders', 'orders.*']) ? 'active' : '' }}" href="#sidebarOrders" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['new.orders', 'orders.*']) ? 'true' : 'false' }}" aria-controls="sidebarOrders">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:bag-smile-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.orders') }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs(['new.orders', 'orders.*']) ? 'show' : '' }}" id="sidebarOrders">
                         <ul class="nav sub-navbar-nav">

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('new.orders') ? 'active' : '' }}" href="{{route('new.orders')}}"> {{ __('messages.order_list') }} </a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('vendor.refund.list') ? 'active' : '' }}" href="{{ route('vendor.refund.list') }}">
                                        {{ __('messages.refund_requests') }}
                                   </a>
                              </li>

                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vendor.sales.report') ? 'active' : '' }}" href="{{ route('vendor.sales.report') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:chart-2-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.sales_report') }} </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:shop-2-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> POS </span>
                    </a>
               </li>



               <li class="menu-title mt-2">{{ __('messages.support') }}</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="#sidebarVendorSupport" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('tickets.*') ? 'true' : 'false' }}" aria-controls="sidebarVendorSupport">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:chat-square-call-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.support') }} </span>
                    </a>
                    <div class="collapse {{ request()->routeIs('tickets.*') ? 'show' : '' }}" id="sidebarVendorSupport">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->input('type') == 'my' || (request()->routeIs('tickets.index') && !request()->has('type')) ? 'active' : '' }}" href="{{ route('tickets.index', ['type' => 'my']) }}"> {{ __('messages.my_tickets') }} </a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->input('type') == 'customer' ? 'active' : '' }}" href="{{ route('tickets.index', ['type' => 'customer']) }}"> {{ __('messages.customer_tickets') }} </a>
                              </li>
                         </ul>
                    </div>
               </li>
               @endif
               <li class="nav-item">
                    <a class="nav-link" href="{{route('logout')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:logout-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.logout') }} </span>
                    </a>
               </li>


               <!-- <li class="menu-title mt-2">Custom</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarPages" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPages">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:gift-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> Pages </span>
                    </a>
                    <div class="collapse" id="sidebarPages">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-starter.html">Welcome</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-comingsoon.html">Coming Soon</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-timeline.html">Timeline</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-pricing.html">Pricing</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-maintenance.html">Maintenance</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-404.html">404 Error</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="pages-404-alt.html">404 Error (alt)</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarAuthentication" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAuthentication">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:lock-keyhole-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> Authentication </span>
                    </a>
                    <div class="collapse" id="sidebarAuthentication">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="auth-signin.html">Sign In</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="auth-signup.html">Sign Up</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="auth-password.html">Reset Password</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="auth-lock-screen.html">Lock Screen</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="widgets.html">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:atom-linear"></iconify-icon>
                         </span>
                         <span class="nav-text">Widgets</span>
                         <span class="badge bg-info badge-pill text-end">9+</span>
                    </a>
               </li> -->

               <!-- <li class="menu-title mt-2">Components</li>

                    <li class="nav-item">
                         <a class="nav-link menu-arrow" href="#sidebarBaseUI" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarBaseUI">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:bookmark-square-linear"></iconify-icon>
                              </span>
                              <span class="nav-text"> Base UI </span>
                         </a>
                         <div class="collapse" id="sidebarBaseUI">
                              <ul class="nav sub-navbar-nav">
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-accordion.html">Accordion</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-alerts.html">Alerts</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-avatar.html">Avatar</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-badge.html">Badge</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-breadcrumb.html">Breadcrumb</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-buttons.html">Buttons</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-card.html">Card</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-carousel.html">Carousel</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-collapse.html">Collapse</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-dropdown.html">Dropdown</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-list-group.html">List Group</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-modal.html">Modal</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-tabs.html">Tabs</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-offcanvas.html">Offcanvas</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-pagination.html">Pagination</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-placeholders.html">Placeholders</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-popovers.html">Popovers</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-progress.html">Progress</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-scrollspy.html">Scrollspy</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-spinners.html">Spinners</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-toasts.html">Toasts</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="ui-tooltips.html">Tooltips</a>
                                   </li>
                              </ul>
                         </div>
                    </li>

                    <li class="nav-item">
                         <a class="nav-link menu-arrow" href="#sidebarExtendedUI" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarExtendedUI">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:case-round-linear"></iconify-icon>
                              </span>
                              <span class="nav-text"> Advanced UI </span>
                         </a>
                         <div class="collapse" id="sidebarExtendedUI">
                              <ul class="nav sub-navbar-nav">
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="extended-ratings.html">Ratings</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="extended-sweetalert.html">Sweet Alert</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="extended-swiper-silder.html">Swiper Slider</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="extended-scrollbar.html">Scrollbar</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="extended-toastify.html">Toastify</a>
                                   </li>
                              </ul>
                         </div>
                    </li>

                    <li class="nav-item">
                         <a class="nav-link menu-arrow" href="#sidebarCharts" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCharts">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:pie-chart-2-linear"></iconify-icon>
                              </span>
                              <span class="nav-text"> Charts </span>
                         </a>
                         <div class="collapse" id="sidebarCharts">
                              <ul class="nav sub-navbar-nav">
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-area.html">Area</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-bar.html">Bar</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-bubble.html">Bubble</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-candlestick.html">Candlestick</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-column.html">Column</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-heatmap.html">Heatmap</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-line.html">Line</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-mixed.html">Mixed</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-timeline.html">Timeline</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-boxplot.html">Boxplot</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-treemap.html">Treemap</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-pie.html">Pie</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-radar.html">Radar</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-radialbar.html">RadialBar</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-scatter.html">Scatter</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="charts-apex-polar-area.html">Polar Area</a>
                                   </li>
                              </ul>
                         </div>
                    </li>

                    <li class="nav-item">
                         <a class="nav-link menu-arrow" href="#sidebarForms" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarForms">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:book-bookmark-linear"></iconify-icon>
                              </span>
                              <span class="nav-text"> Forms </span>
                         </a>
                         <div class="collapse" id="sidebarForms">
                              <ul class="nav sub-navbar-nav">
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-basic.html">Basic Elements</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-checkbox-radio.html">Checkbox &amp; Radio</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-choices.html">Choice Select</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-clipboard.html">Clipboard</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-flatepicker.html">Flatepicker</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-validation.html">Validation</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-wizard.html">Wizard</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-fileuploads.html">File Upload</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-editors.html">Editors</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-input-mask.html">Input Mask</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="forms-range-slider.html">Slider</a>
                                   </li>
                              </ul>
                         </div>
                    </li>

                    <li class="nav-item">
                         <a class="nav-link menu-arrow" href="#sidebarTables" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTables">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:tuning-2-linear"></iconify-icon>
                              </span>
                              <span class="nav-text"> Tables </span>
                         </a>
                         <div class="collapse" id="sidebarTables">
                              <ul class="nav sub-navbar-nav">
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="tables-basic.html">Basic Tables</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="tables-gridjs.html">Grid Js</a>
                                   </li>
                              </ul>
                         </div>
                    </li>

                    <li class="nav-item">
                         <a class="nav-link menu-arrow" href="#sidebarIcons" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarIcons">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:ufo-2-linear"></iconify-icon>
                              </span>
                              <span class="nav-text"> Icons </span>
                         </a>
                         <div class="collapse" id="sidebarIcons">
                              <ul class="nav sub-navbar-nav">
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="icons-boxicons.html">Boxicons</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="icons-solar.html">Solar Icons</a>
                                   </li>
                              </ul>
                         </div>
                    </li>

                    <li class="nav-item">
                         <a class="nav-link menu-arrow" href="#sidebarMaps" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMaps">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:streets-map-point-linear"></iconify-icon>
                              </span>
                              <span class="nav-text"> Maps </span>
                         </a>
                         <div class="collapse" id="sidebarMaps">
                              <ul class="nav sub-navbar-nav">
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="maps-google.html">Google Maps</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="maps-vector.html">Vector Maps</a>
                                   </li>
                              </ul>
                         </div>
                    </li>

                    <li class="nav-item">
                         <a class="nav-link" href="javascript:void(0);">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:volleyball-linear"></iconify-icon>
                              </span>
                              <span class="nav-text">Badge Menu</span>
                              <span class="badge bg-danger badge-pill text-end">1</span>
                         </a>
                    </li>

                    <li class="nav-item">
                         <a class="nav-link menu-arrow" href="#sidebarMultiLevelDemo" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMultiLevelDemo">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:share-circle-linear"></iconify-icon>
                              </span>
                              <span class="nav-text"> Menu Item </span>
                         </a>
                         <div class="collapse" id="sidebarMultiLevelDemo">
                              <ul class="nav sub-navbar-nav">
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link" href="javascript:void(0);">Menu Item 1</a>
                                   </li>
                                   <li class="sub-nav-item">
                                        <a class="sub-nav-link  menu-arrow" href="#sidebarItemDemoSubItem" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarItemDemoSubItem">
                                             <span> Menu Item 2 </span>
                                        </a>
                                        <div class="collapse" id="sidebarItemDemoSubItem">
                                             <ul class="nav sub-navbar-nav">
                                                  <li class="sub-nav-item">
                                                       <a class="sub-nav-link" href="javascript:void(0);">Menu Sub item</a>
                                                  </li>
                                             </ul>
                                        </div>
                                   </li>
                              </ul>
                         </div>
                    </li>

                    <li class="nav-item">
                         <a class="nav-link disabled" href="javascript:void(0);">
                              <span class="nav-icon">
                                   <iconify-icon icon="solar:user-block-rounded-linear"></iconify-icon>
                              </span>
                              <span class="nav-text"> Disable Item </span>
                         </a>
                    </li>
                     -->
          </ul>
     </div>
</div>
@endif
<!-- ========== App Menu End ========== -->