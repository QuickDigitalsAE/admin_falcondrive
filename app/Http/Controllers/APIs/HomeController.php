<?php

namespace App\Http\Controllers\APIs;

use App\Http\Resources\AboutUsResource;
use App\Http\Resources\BlogResource;
use App\Http\Resources\CarResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\FaqResource;
use App\Http\Resources\HighlightResource;
use App\Http\Resources\PromotionResource;
use App\Http\Resources\SettingResource;
use App\Http\Resources\TestimonialResource;
use App\Models\AboutUs;
use App\Models\Blog;
use App\Models\Car;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Highlight;
use App\Models\Promotion;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Traits\ApiResponseTrait;

class HomeController
{
    use ApiResponseTrait;

    public function __invoke()
    {
        $settingsCollection = Setting::where('group', 'site')->orderBy('order')->get();
        $settings = $settingsCollection->keyBy('key');
        $featuredCars = Car::with(['brand', 'categories'])
            ->where('featured', 1)
            ->orderBy('featured_sorting', 'asc')
            ->orderBy('id', 'desc')
            ->limit(12)
            ->get();

        $topOffers = Promotion::where('top_offer', 1)->latest('id')->limit(6)->get();
        $latestBlogs = Blog::latest('blog_schedule')->limit(6)->get();

        return $this->successResponse('Home page data fetched successfully', [
            'meta_data' => [
                'title_en' => $this->settingValue($settings, ['site.title_en'], $this->settingValue($settings, ['messages_home_h1_1_en'])),
                'title_ar' => $this->settingValue($settings, ['site.title_ar'], $this->settingValue($settings, ['messages_home_h1_1_ar'])),
                'description_en' => $this->settingValue($settings, ['site.description_en'], $this->settingValue($settings, ['messages_home_p_en'])),
                'description_ar' => $this->settingValue($settings, ['site.description_ar'], $this->settingValue($settings, ['messages_home_p_ar'])),
            ],
            'highlights' => HighlightResource::collection(Highlight::orderedForListing()->get())->resolve(),
            'featured_cars' => CarResource::collection($featuredCars)->resolve(),
            'categories' => CategoryResource::collection(Category::orderBy('name_en')->get())->resolve(),
            'top_promotions' => PromotionResource::collection($topOffers)->resolve(),
            'about_us' => AboutUsResource::collection(AboutUs::latest('id')->limit(1)->get())->resolve(),
            'testimonials' => TestimonialResource::collection(Testimonial::latest('id')->limit(10)->get())->resolve(),
            'faqs' => FaqResource::collection(Faq::latest('id')->limit(10)->get())->resolve(),
            'latest_blogs' => BlogResource::collection($latestBlogs)->resolve(),
            'site_settings' => SettingResource::collection(
                $settingsCollection
            )->resolve(),
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
}
