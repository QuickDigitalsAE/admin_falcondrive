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
        $featuredCars = Car::with(['brand', 'categories'])
            ->where('featured', 1)
            ->orderByRaw('CAST(COALESCE(sorting, 999999) AS UNSIGNED) ASC')
            ->limit(12)
            ->get();

        $latestCars = Car::with(['brand', 'categories'])
            ->latest('id')
            ->limit(12)
            ->get();

        $topOffers = Promotion::where('top_offer', 1)->latest('id')->limit(6)->get();
        $latestBlogs = Blog::latest('blog_schedule')->limit(6)->get();

        return $this->successResponse('Home page data fetched successfully', [
            'highlights' => HighlightResource::collection(Highlight::latest('id')->get())->resolve(),
            'featured_cars' => CarResource::collection($featuredCars)->resolve(),
            'latest_cars' => CarResource::collection($latestCars)->resolve(),
            'categories' => CategoryResource::collection(Category::orderBy('name_en')->get())->resolve(),
            'top_promotions' => PromotionResource::collection($topOffers)->resolve(),
            'about_us' => AboutUsResource::collection(AboutUs::latest('id')->limit(1)->get())->resolve(),
            'testimonials' => TestimonialResource::collection(Testimonial::latest('id')->limit(10)->get())->resolve(),
            'faqs' => FaqResource::collection(Faq::latest('id')->limit(10)->get())->resolve(),
            'latest_blogs' => BlogResource::collection($latestBlogs)->resolve(),
            'site_settings' => SettingResource::collection(Setting::orderBy('order')->get())->resolve(),
        ]);
    }
}
