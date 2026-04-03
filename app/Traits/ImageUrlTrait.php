<?php

namespace App\Traits;
use Illuminate\Support\Facades\Storage;

trait ImageUrlTrait
{
    public function getImageUrl($image_path)
    {
        $image_path = Storage::url($image_path);
        return asset($image_path);
    }
}
