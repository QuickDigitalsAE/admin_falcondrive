<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CarRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['name_en', 'name_ar', 'description_en', 'description_ar', 'price_daily', 'price_weekly', 'price_monthly', 'full_insurance_amount', 'additional_driver_amount', 'baby_seat_amount', 'deposit_amount', 'waiver_amount', 'different_city_dropoff_fee', 'main_image', 'images', 'model', 'featured', 'featured_sorting', 'engine', 'seats', 'doors', 'deposit', 'luggage', 'cruise_control', 'bluetooth', 'automatic', 'parking_sensor', 'navigation', 'carplay', 'camera', 'slug', 'seo_title_en', 'seo_title_ar', 'seo_brief_en', 'seo_brief_ar', 'brand_id', 'stock', 'cdw_daily', 'cdw_weekly', 'cdw_monthly', 'sorting'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'name_en' => ['required','string','max:191'],
            'name_ar' => ['required','string','max:191'],
            'description_en' => ['nullable','string'],
            'description_ar' => ['nullable','string'],
            'price_daily' => ['nullable','string','max:191'],
            'price_weekly' => ['nullable','string','max:191'],
            'price_monthly' => ['nullable','string','max:191'],
            'full_insurance_amount' => ['nullable','string','max:191'],
            'additional_driver_amount' => ['nullable','string','max:191'],
            'baby_seat_amount' => ['nullable','string','max:191'],
            'deposit_amount' => ['nullable','string','max:191'],
            'waiver_amount' => ['nullable','string','max:191'],
            'different_city_dropoff_fee' => ['nullable','string','max:191'],
            'main_image' => ['nullable','string','max:191'],
            'images' => ['nullable','string','max:191'],
            'model' => ['nullable','string','max:191'],
            'featured' => ['nullable','integer'],
            'featured_sorting' => ['nullable','integer'],
            'engine' => ['nullable','string','max:191'],
            'seats' => ['nullable','string','max:191'],
            'doors' => ['nullable','string','max:191'],
            'deposit' => ['nullable','string','max:191'],
            'luggage' => ['nullable','string','max:191'],
            'cruise_control' => ['nullable','integer'],
            'bluetooth' => ['nullable','integer'],
            'automatic' => ['nullable','integer'],
            'parking_sensor' => ['nullable','integer'],
            'navigation' => ['nullable','integer'],
            'carplay' => ['nullable','integer'],
            'camera' => ['nullable','integer'],
            'slug' => ['nullable','string','max:191'],
            'seo_title_en' => ['nullable','string','max:191'],
            'seo_title_ar' => ['nullable','string','max:191'],
            'seo_brief_en' => ['nullable','string'],
            'seo_brief_ar' => ['nullable','string'],
            'brand_id' => ['nullable','integer'],
            'stock' => ['nullable','integer'],
            'cdw_daily' => ['nullable','string','max:191'],
            'cdw_weekly' => ['nullable','string','max:191'],
            'cdw_monthly' => ['nullable','string','max:191'],
            'sorting' => ['nullable','string','max:191'],
            'slug' => ['required','string','max:191'],
                        'brand_id' => ['required','integer','exists:brands,id']
        ];
    }
}
