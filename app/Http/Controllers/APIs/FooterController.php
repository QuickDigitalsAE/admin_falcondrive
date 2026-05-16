<?php

namespace App\Http\Controllers\APIs;

use App\Models\AboutUs;
use App\Models\Setting;
use App\Traits\ApiResponseTrait;

class FooterController
{
    use ApiResponseTrait;

    public function __invoke()
    {
        $settings = Setting::query()
            ->where('group', 'site')
            ->orderBy('order')
            ->get()
            ->keyBy('key');

        $baseUrl = rtrim((string) config('app.url'), '/');
        $aboutUs = AboutUs::query()->latest('id')->first();

        return $this->successResponse('Data fetched successfully', [
            'footer_details' => [
                'logo' => $this->settingAssetUrl($settings, [
                    'footer_logo',
                    'site_logo',
                    'site.logo',
                    'website_logo',
                    'logo',
                ]),
                'aboutus_en' => $this->settingValue($settings, ['messages_about_en'], 'About Us'),
                'aboutus_ar' => $this->settingValue($settings, ['messages_about_ar'], 'من نحن'),
                'about_text_en' => $this->settingValue(
                    $settings,
                    ['site.footer_en', 'about_footer_en'],
                    $aboutUs?->first_section_en
                ),
                'about_text_ar' => $this->settingValue(
                    $settings,
                    ['site.footer_ar', 'about_footer_ar'],
                    $aboutUs?->first_section_ar
                ),
                'address_title_en' => $this->settingValue($settings, ['messages_address_en'], 'Address'),
                'address_title_ar' => $this->settingValue($settings, ['messages_address_ar'], 'العنوان'),
                'location_label_en' => $this->settingValue($settings, ['messages_location_en'], 'LOCATION'),
                'location_label_ar' => $this->settingValue($settings, ['messages_location_ar'], 'الموقع'),
                'address_en' => $this->settingValue($settings, ['site.location_en', 'contact_address_en']),
                'address_ar' => $this->settingValue($settings, ['site.location_ar', 'contact_address_ar']),
                'map_url' => $this->settingValue($settings, ['map_url', 'google_map_url']),
                'hours_title_en' => $this->settingValue($settings, ['messages_hours_en'], 'Working Hours'),
                'hours_title_ar' => $this->settingValue($settings, ['messages_hours_ar'], 'ساعات العمل'),
                'hours_en' => $this->settingValue($settings, ['site.hours_en', 'hours_en']),
                'hours_ar' => $this->settingValue($settings, ['site.hours_ar', 'hours_ar']),
                'call_title_en' => $this->settingValue($settings, ['messages_call_us_en'], 'Call Us Now'),
                'call_title_ar' => $this->settingValue($settings, ['messages_call_us_ar'], 'اتصل بنا الان'),
                'hello_title_en' => $this->settingValue($settings, ['messages_hello_en'], 'Say Hello'),
                'hello_title_ar' => $this->settingValue($settings, ['messages_hello_ar'], 'شاركنا'),
                'number' => $this->settingValue($settings, [
                    'contact_phone',
                    'site.number',
                    'support_phone',
                    'phone',
                    'mobile',
                ], ''),
                'email' => $this->settingValue($settings, [
                    'contact_email',
                    'site.email',
                    'support_email',
                    'email',
                ], 'sales@falcondrive.ae'),
            ],
            'footer_links' => [
                [
                    'key' => 'about_us',
                    'aboutus_en' => $this->settingValue($settings, ['messages_about_en'], 'About Us'),
                    'aboutus_ar' => $this->settingValue($settings, ['messages_about_ar'], 'من نحن'),
                    'url' => 'about-us',
                ],
                [
                    'key' => 'fleet',
                    'fleet_en' => $this->settingValue($settings, ['messages_fleet_en'], 'Our Fleet'),
                    'fleet_ar' => $this->settingValue($settings, ['messages_fleet_ar'], 'الاسطول'),
                    'url' => 'our-fleet',
                ],
                [
                    'key' => 'blog',
                    'blog_en' => $this->settingValue($settings, ['messages_blog_en'], 'Blog'),
                    'blog_ar' => $this->settingValue($settings, ['messages_blog_ar'], 'المدونة'),
                    'url' => 'blogs',
                ],
                [
                    'key' => 'faq',
                    'faq_en' => $this->settingValue($settings, ['messages_faq_en'], 'FAQ'),
                    'faq_ar' => $this->settingValue($settings, ['messages_faq_ar'], 'الأسئلة الأكثر شيوعا'),
                    'url' => 'faqs',
                ],
                [
                    'key' => 'contact',
                    'contact_en' => $this->settingValue($settings, ['messages_contact_en'], 'Contact Us'),
                    'contact_ar' => $this->settingValue($settings, ['messages_contact_ar'], 'اتصل بنا'),
                    'url' => 'contact-us',
                ],
                [
                    'key' => 'loyalty',
                    'loyalty_en' => $this->settingValue($settings, ['messages_loyalty_en'], 'Loyalty Program'),
                    'loyalty_ar' => $this->settingValue($settings, ['messages_loyalty_ar'], 'برنامج الولاء'),
                    'url' => 'loyalty-program',
                ],
                [
                    'key' => 'cars_with_driver',
                    'cars_with_driver_en' => $this->settingValue($settings, ['messages_cars_with_driver_en'], 'Cars with Driver'),
                    'cars_with_driver_ar' => $this->settingValue($settings, ['messages_cars_with_driver_ar'], 'سيارات مع سائق'),
                    'url' => 'other/cars-with-driver',
                ],
            ],
            'social_links' => [
                [
                    'platform' => 'facebook',
                    'url' => $this->settingValue($settings, ['facebook_url', 'social_facebook'], 'https://www.facebook.com/falcondrivecarrental/'),
                ],
                [
                    'platform' => 'instagram',
                    'url' => $this->settingValue($settings, ['instagram_url', 'social_instagram'], 'https://www.instagram.com/falcondrive.ae/?igshid=MzRlODBiNWFlZA%3D%3D'),
                ],
            ],
            'policy_links' => [
                [
                    'key' => 'privacy',
                    'label_en' => $this->settingValue($settings, ['messages_privacy_en'], 'Privacy Policy'),
                    'label_ar' => $this->settingValue($settings, ['messages_privacy_ar'], 'سياسة الخصوصية'),
                    'url' => 'privacy-policy',
                ],
                [
                    'key' => 'terms',
                    'label_en' => $this->settingValue($settings, ['messages_terms_en'], 'Terms and Conditions'),
                    'label_ar' => $this->settingValue($settings, ['messages_terms_ar'], 'الشروط والأحكام'),
                    'url' => 'terms-and-conditions',
                ],
            ]
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
