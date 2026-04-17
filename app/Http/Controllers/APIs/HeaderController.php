<?php

namespace App\Http\Controllers\APIs;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Lease;
use App\Models\Setting;
use App\Traits\ApiResponseTrait;

class HeaderController
{
    use ApiResponseTrait;

    public function __invoke()
    {
        $settings = Setting::query()
            ->where('group', 'site')
            ->orderBy('order')
            ->get(['key', 'value', 'type'])
            ->keyBy('key');

        $baseUrl = rtrim((string) config('app.url'), '/');

        $brands = Brand::query()
            ->orderBy('name_en')
            ->get()
            ->map(function (Brand $brand) use ($baseUrl) {
                return [
                    'slug' => $brand->slug,
                    'name_en' => $brand->name_en,
                    'name_ar' => $brand->name_ar,
                    'logo' => $brand->logo,
                    'logo_url' => $brand->logo_url,
                ];
            })
            ->values()
            ->all();

        $categories = Category::query()
            ->orderBy('name_en')
            ->get()
            ->map(function (Category $category) use ($baseUrl) {
                return [
                    'slug' => $category->slug,
                    'name_en' => $category->name_en,
                    'name_ar' => $category->name_ar
                ];
            })
            ->values()
            ->all();

        array_unshift($categories, [
            'slug' => 'cars',
            'name_en' => $this->settingValue($settings, ['messages_all_cars_en'], 'All Cars'),
            'name_ar' => $this->settingValue($settings, ['messages_all_cars_ar'], 'جميع السيارات'),
        ]);

        $lease = Lease::query()
            ->orderBy('name_en')
            ->get()
            ->map(function (Lease $item) use ($baseUrl) {
                return [
                    'slug' => $item->slug,
                    'title_en' => $item->name_en,
                    'title_ar' => $item->name_ar
                ];
            })
            ->values()
            ->all();

        return $this->successResponse('Data fetched successfully', [
            'header_details' => [
                'logo' => $this->settingAssetUrl($settings, [
                    'site_logo',
                    'site.logo',
                    'website_logo',
                    'logo',
                ]),
                'email' => $this->settingValue($settings, [
                    'contact_email',
                    'site.email',
                    'support_email',
                    'email',
                ], 'sales@falcondrive.ae'),
                'number' => $this->settingValue($settings, [
                    'contact_phone',
                    'site.number',
                    'support_phone',
                    'phone',
                    'mobile',
                ], ''),
                
                'inquiry_button_en' => $this->settingValue($settings, ['messages_send_enquiry_en'], 'Inquiry'),
                'inquiry_button_ar' => $this->settingValue($settings, ['messages_send_enquiry_ar'], 'ارسل الاستعلام'),
            ],
            'menu_items' => [
                'home_en' => $this->settingValue($settings, ['messages_home_en'], 'Home'),
                'home_ar' => $this->settingValue($settings, ['messages_home_ar'], 'الرئيسية'),
                'aboutus_en' => $this->settingValue($settings, ['messages_about_en'], 'About Us'),
                'aboutus_ar' => $this->settingValue($settings, ['messages_about_ar'], 'من نحن'),
                'brand_en' => $this->settingValue($settings, ['messages_brands_en'], 'Our Brands'),
                'brand_ar' => $this->settingValue($settings, ['messages_brands_ar'], 'ماركات السيارات'),
                'fleet_en' => $this->settingValue($settings, ['messages_fleet_en'], 'Our Fleet'),
                'fleet_ar' => $this->settingValue($settings, ['messages_fleet_ar'], 'الاسطول'),
                'blog_en' => $this->settingValue($settings, ['messages_blog_en'], 'Blog'),
                'blog_ar' => $this->settingValue($settings, ['messages_blog_ar'], 'المدونة'),
                'contact_en' => $this->settingValue($settings, ['messages_contact_en'], 'Contact Us'),
                'contact_ar' => $this->settingValue($settings, ['messages_contact_ar'], 'اتصل بنا'),
                'promotions_en' => $this->settingValue($settings, ['messages_promotions_en'], 'Promotions'),
                'promotions_ar' => $this->settingValue($settings, ['messages_promotions_ar'], 'العروض'),
                'locations_en' => $this->settingValue($settings, ['messages_locations_en'], 'Locations'),
                'locations_ar' => $this->settingValue($settings, ['messages_locations_ar'], 'المواقع'),
                'lease_en' => $this->settingValue($settings, ['messages_lease_en'], 'Lease'),
                'lease_ar' => $this->settingValue($settings, ['messages_lease_ar'], 'الاستئجار'),
            ],
            'categories' => $categories,
            'brands' => $brands,
            'lease' => $lease,
        ]);
    }

    private function settingValue($settings, array $keys, ?string $default = null): ?string
    {
        foreach ($keys as $key) {
            $value = $settings->get($key)?->value;
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return $default;
    }

    private function settingAssetUrl($settings, array $keys): ?string
    {
        foreach ($keys as $key) {
            $setting = $settings->get($key);
            if (!$setting) {
                continue;
            }

            $assetUrl = $setting->value_url ?? null;
            if ($assetUrl) {
                return $assetUrl;
            }

            if ($setting->value) {
                return (string) $setting->value;
            }
        }

        return null;
    }
}
