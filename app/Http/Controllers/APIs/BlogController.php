<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\BlogRequest;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class BlogController extends BaseApiController
{
    protected string $modelClass = Blog::class;
    protected string $resourceClass = BlogResource::class;
    protected string $storeRequestClass = BlogRequest::class;
    protected string $updateRequestClass = BlogRequest::class;
    protected array $searchable = ['title_en', 'title_ar', 'slug'];
    protected array $with = [];
    protected string $publicMessage = 'Blog list fetched successfully';
    protected string $singleMessage = 'Blog fetched successfully';
    protected string $storeMessage = 'Blog created successfully';
    protected string $updateMessage = 'Blog updated successfully';
    protected string $deleteMessage = 'Blog deleted successfully';
    protected array $metaDataKeys = [
        'title_en' => ['messages_blogs_title_en'],
        'title_ar' => ['messages_blogs_title_ar'],
        'description_en' => ['messages_blogs_brief_en'],
        'description_ar' => ['messages_blogs_brief_ar'],
    ];

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function publicShow(\App\Models\Blog $blog)
    {
        abort_unless($blog->publishedAt()?->lte(now(config('app.timezone'))), 404);

        $recentPosts = Blog::query()
            ->whereKeyNot($blog->id)
            ->publiclyAvailable()
            ->orderByRaw('COALESCE(blog_schedule, created_at) DESC')
            ->limit(5)
            ->get()
            ->map(fn (Blog $recentBlog) => [
                'image' => $recentBlog->image,
                'image_url' => $recentBlog->image_url,
                'slug' => $recentBlog->slug,
                'title_en' => $recentBlog->title_en,
                'title_ar' => $recentBlog->title_ar,
                'post_datetime' => optional($recentBlog->publishedAt())?->toISOString(),
            ])
            ->values()
            ->all();

        return $this->successResponse($this->singleMessage, array_merge(
            $this->transform($blog->load($this->with)),
            ['recent_posts' => $recentPosts]
        ));
    }

    protected function query(Request $request): Builder
    {
        $query = parent::query($request)->publiclyAvailable();

        if (!$request->filled('sort_by')) {
            $query->reorder()->orderByRaw('COALESCE(blog_schedule, created_at) DESC');
        }

        return $query;
    }

}
