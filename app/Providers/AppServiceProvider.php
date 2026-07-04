<?php

namespace App\Providers;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Category;
use App\Models\NotificationSetting;
use App\Models\VendorPolicy;




use App\Models\GeneralSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        \Illuminate\Support\Facades\App::setLocale(session('locale', 'en'));

        \App\Models\Product::observe(\App\Models\Observers\SitemapObserver::class);
        \App\Models\Category::observe(\App\Models\Observers\SitemapObserver::class);
        \App\Models\SubCategory::observe(\App\Models\Observers\SitemapObserver::class);
        \App\Models\ChildCategory::observe(\App\Models\Observers\SitemapObserver::class);
        \App\Models\Brand::observe(\App\Models\Observers\SitemapObserver::class);
        \App\Models\Blog::observe(\App\Models\Observers\SitemapObserver::class);

        try {
            $timezone = \App\Models\GeneralSetting::where('key', 'timezone')->value('value');
            if ($timezone) {
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            }
        } catch (\Exception $e) {
            // Silently fail if DB is not ready
        }

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            try {
                if (!str_contains($view->getName(), 'errors::')) {
                    $categories = Category::with(['subCategories.childCategories'])->where('is_active', 1)->get();
                    $notificationSetting = NotificationSetting::first();
                    
                    // Fetch site settings
                    $siteSettings = GeneralSetting::whereIn('key', ['website_logo_dark', 'website_logo_light', 'favicon', 'website_name', 'contact_phone', 'contact_email', 'address'])->get()->pluck('value', 'key');
                    $siteLogoDark = $siteSettings['website_logo_dark'] ?? null;
                    $siteLogoLight = $siteSettings['website_logo_light'] ?? null;
                    $siteFavicon = $siteSettings['favicon'] ?? null;
                    $siteName = $siteSettings['website_name'] ?? 'Aariva';
                    $contactPhone = $siteSettings['contact_phone'] ?? null;
                    $contactEmail = $siteSettings['contact_email'] ?? null;
                    $address = $siteSettings['address'] ?? null;

                    $activeVendorPolicy = null;
                    if (Auth::check() && Auth::user()->role == '2' && Auth::user()->agreement == 0) {
                        $activeVendorPolicy = VendorPolicy::where('status', 1)->latest('id')->first();
                    }

                    $view->with([
                        'categories' => $categories,
                        'notificationSetting' => $notificationSetting,
                        'activeVendorPolicy' => $activeVendorPolicy,
                        'siteLogoDark' => $siteLogoDark,
                        'siteLogoLight' => $siteLogoLight,
                        'siteFavicon' => $siteFavicon,
                        'siteName' => $siteName,
                        'contactPhone' => $contactPhone,
                        'contactEmail' => $contactEmail,
                        'contactAddress' => $address,
                    ]);
                }
            } catch (\Exception $e) {
                $view->with([
                    'siteName' => 'Aariva',
                    'siteFavicon' => null,
                    'siteLogoDark' => null,
                    'siteLogoLight' => null,
                    'categories' => collect(),
                    'notificationSetting' => null,
                    'activeVendorPolicy' => null,
                ]);
            }
        });
    }
}
