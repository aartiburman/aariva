<!-- ========== App Menu Start ========== -->
@if(Auth::user()->role == 1)
<div class="main-nav ">
     <!-- Sidebar Logo -->
     <div class="logo-box">
          <a href="{{route('admin.dashboard')}}" class="logo-dark">
               <img src="{{ $siteLogoDark ? asset('uploads/settings/'.$siteLogoDark) : asset('backend/assets/images/small-logo.png') }}" class="logo-sm small_logo_size" alt="logo sm">
               <img src="{{ $siteLogoDark ? asset('uploads/settings/'.$siteLogoDark) : asset('backend/assets/images/logo.png') }}" class="logo-lg " alt="logo dark">
          </a>

          <a href="{{route('admin.dashboard')}}" class="logo-light">
               <img src="{{ $siteLogoLight ? asset('uploads/settings/'.$siteLogoLight) : asset('backend/assets/images/small-logo.png') }}" class="logo-sm small_logo_size" alt="logo sm">
               <img src="{{ $siteLogoLight ? asset('uploads/settings/'.$siteLogoLight) : asset('backend/assets/images/logo.png') }}" class="logo-lg" alt="logo light">
          </a>
     </div>

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
                    <a class="nav-link menu-arrow {{ request()->routeIs(['vendors.list','vendor.requests','vendor.payout','edit.vendor','add.vendor']) ? 'active' : '' }}" href="#sidebarVendorManagement" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['vendors.*', 'vendor.*']) ? 'true' : 'false' }}" aria-controls="sidebarVendorManagement">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon>
                         </span>
                         <span class="nav-text">{{ __('messages.vendor_management') }}</span>
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
                    </a>
                    <div class="collapse {{ request()->routeIs(['vendors.list','vendor.requests','vendor.payout','edit.vendor','add.vendor']) ? 'show' : '' }}" id="sidebarVendorManagement">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('vendors.list') ? 'active' : '' }}" href="{{ route('vendors.list') }}">{{ __('messages.vendor')  }} {{ __('messages.list')  }} </a>
                              </li>

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('vendor.requests') ? 'active' : '' }}" href="{{route('vendor.requests')}}">{{ __('messages.vendor')  }} {{ __('messages.request')  }} </a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('vendor.payout') ? 'active' : '' }}" href="{{ route('vendor.payout') }}"> {{ __('messages.payouts')  }} </a>
                              </li>
                             
                         </ul>
                     </div>
                </li>

                <li class="nav-item">
                     <a class="nav-link menu-arrow {{ request()->routeIs(['category.*','add.category', 'edit.category','subcategory.*', 'child.category.*', 'brand.*']) ? 'active' : '' }}" href="#sidebarCategory" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['category.*', 'subcategory.*', 'child.category.*', 'brand.*']) ? 'true' : 'false' }}" aria-controls="sidebarCategory">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:clipboard-list-linear"></iconify-icon>
                         </span>
                         <span class="nav-text">{{ __('messages.category_and_brands') }}</span>
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
                    </a>
                    <div class="collapse {{ request()->routeIs(['category.*','add.category', 'edit.category', 'subcategory.*', 'child.category.*', 'brand.*']) ? 'show' : '' }}" id="sidebarCategory">
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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


               {{-- ================= INVENTORY MANAGEMENT ================= --}}
               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['inventory.*']) ? 'active' : '' }}" href="#sidebarInventory" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['inventory.*']) ? 'true' : 'false' }}" aria-controls="sidebarInventory">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:box-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> Inventory </span>
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
                    </a>
                    <div class="collapse {{ request()->routeIs(['inventory.*']) ? 'show' : '' }}" id="sidebarInventory">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}" href="{{ route('inventory.dashboard') }}">Dashboard</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('inventory.movements') ? 'active' : '' }}" href="{{ route('inventory.movements') }}">Stock Movements</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('inventory.warehouses') ? 'active' : '' }}" href="{{ route('inventory.warehouses') }}">Warehouses</a>
                              </li>
                         </ul>
                    </div>
               </li>

               {{-- ================= CRM ================= --}}
               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['crm.*']) ? 'active' : '' }}" href="#sidebarCrm" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['crm.*']) ? 'true' : 'false' }}" aria-controls="sidebarCrm">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> CRM </span>
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
                    </a>
                    <div class="collapse {{ request()->routeIs(['crm.*']) ? 'show' : '' }}" id="sidebarCrm">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('crm.dashboard') ? 'active' : '' }}" href="{{ route('crm.dashboard') }}">Dashboard</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('crm.customers') ? 'active' : '' }}" href="{{ route('crm.customers') }}">All Customers</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('crm.groups') ? 'active' : '' }}" href="{{ route('crm.groups') }}">Groups</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('crm.abandoned.carts') ? 'active' : '' }}" href="{{ route('crm.abandoned.carts') }}">Abandoned Carts</a>
                              </li>
                         </ul>
                    </div>
               </li>

               {{-- ================= SUPPLIER MANAGEMENT ================= --}}
               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['supplier.*']) ? 'active' : '' }}" href="#sidebarSuppliers" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['supplier.*']) ? 'true' : 'false' }}" aria-controls="sidebarSuppliers">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:delivery-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> Suppliers </span>
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
                    </a>
                    <div class="collapse {{ request()->routeIs(['supplier.*']) ? 'show' : '' }}" id="sidebarSuppliers">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('supplier.index') ? 'active' : '' }}" href="{{ route('supplier.index') }}">All Suppliers</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('supplier.purchase.orders') ? 'active' : '' }}" href="{{ route('supplier.purchase.orders') }}">Purchase Orders</a>
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
                    </a>
                    <div class="collapse {{ request()->routeIs(['coupons.*']) ? 'show' : '' }}" id="sidebarCoupons">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('coupons.list') ? 'active' : '' }}" href="{{ route('coupons.list') }}">{{ __('messages.list') }}</a>     
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('coupon.create') ? 'active' : '' }}" href="{{ route('coupons.create') }}">{{ __('messages.add') }}</a>
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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



               <li class="menu-title mt-2">{{ __('messages.support') }}</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="#sidebarSupport" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('tickets.*') ? 'true' : 'false' }}" aria-controls="sidebarSupport">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:chat-square-call-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.support') }} </span>
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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
                                   <a class="sub-nav-link {{ request()->input('type') == 'escalated' ? 'active' : '' }}" href="{{ route('tickets.index', ['type' => 'escalated']) }}">Escalated Tickets</a>
                              </li>
                         </ul>
                    </div>
               </li>


               <li class="menu-title mt-2">{{ __('messages.other') }}</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow {{ request()->routeIs(['general.setting', 'payment.getway.setting', 'email.setting', 'sms.setting', 'notification.setting']) ? 'active' : '' }}" href="#sidebarWebsiteSettings" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['general.setting', 'payment.getway.setting', 'email.setting', 'sms.setting', 'notification.setting']) ? 'true' : 'false' }}" aria-controls="sidebarWebsiteSettings">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:settings-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> {{ __('messages.website_settings') ?? 'Website Settings' }} </span>
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
                    </a>
                    <div class="collapse {{ request()->routeIs(['general.setting', 'payment.getway.setting', 'email.setting', 'sms.setting', 'notification.setting']) ? 'show' : '' }}" id="sidebarWebsiteSettings">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('general.setting') ? 'active' : '' }}" href="{{route('general.setting')}}">{{ __('messages.general_settings') ?? 'General Settings' }}</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('company.info') ? 'active' : '' }}" href="{{route('company.info')}}">Company Info</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('kyc.documents.list') ? 'active' : '' }}" href="{{route('kyc.documents.list')}}">KYC Documents</a>
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
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('notification.setting') ? 'active' : '' }}" href="{{route('notification.setting')}}">{{ __('messages.notification_setting') ?? 'Notification Setting' }}</a>
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
                    <a class="nav-link menu-arrow {{ request()->routeIs(['whatsapp.*']) ? 'active' : '' }}" href="#sidebarWhatsApp" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['whatsapp.*']) ? 'true' : 'false' }}" aria-controls="sidebarWhatsApp">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:chat-round-call-linear"></iconify-icon>
                         </span>
                         <span class="nav-text"> WhatsApp </span>
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
                    </a>
                    <div class="collapse {{ request()->routeIs(['whatsapp.*']) ? 'show' : '' }}" id="sidebarWhatsApp">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('whatsapp.settings') ? 'active' : '' }}" href="{{ route('whatsapp.settings') }}">Settings</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link {{ request()->routeIs('whatsapp.messages') ? 'active' : '' }}" href="{{ route('whatsapp.messages') }}">Message Log</a>
                              </li>
                         </ul>
                    </div>
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
               <img src="{{ $siteLogoDark ? asset('uploads/settings/'.$siteLogoDark) : asset('backend/assets/images/small-logo.png') }}" class="logo-sm small_logo_size" alt="logo sm">
               <img src="{{ $siteLogoDark ? asset('uploads/settings/'.$siteLogoDark) : asset('backend/assets/images/logo.png') }}" class="logo-lg" alt="logo dark">
          </a>

          <a href="{{route('vendor.dashboard')}}" class="logo-light">
               <img src="{{ $siteLogoLight ? asset('uploads/settings/'.$siteLogoLight) : asset('backend/assets/images/small-logo.png') }}" class="logo-sm small_logo_size" alt="logo sm">
               <img src="{{ $siteLogoLight ? asset('uploads/settings/'.$siteLogoLight) : asset('backend/assets/images/logo.png') }}" class="logo-lg" alt="logo light">
          </a>
     </div>

     <div class="vendor-profile-section p-3 mb-2">
          <div class="d-flex align-items-center gap-3">
               <div class="vendor-avatar">
                  
                    @if(Auth::user()->image)
              
                    <img src="{{ \App\Helpers\ImageHelper::getVendorsImage(Auth::user()->image) }}" alt="vendor logo" class="rounded-circle border border-2 border-white-50" width="48" height="48" style="object-fit: cover;">
                    @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold border border-2 border-white-50" style="width: 48px; height: 48px; font-size: 20px;">
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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
                         <iconify-icon icon="solar:alt-arrow-right-linear" class="nav-arrow"></iconify-icon>
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

               <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vendor.delivery.settings') ? 'active' : '' }}" href="{{route('vendor.delivery.settings')}}">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:delivery-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Delivery Settings </span>
                    </a>
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

          </ul>
     </div>
</div>
@endif
<!-- ========== App Menu End ========== -->
