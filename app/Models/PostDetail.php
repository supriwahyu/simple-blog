<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PostDetail extends Model
{
    /** @use HasFactory<\Database\Factories\PostDetailFactory> */
    use HasFactory;

    protected $fillable = ['post_id', 'title', 'excerpt', 'content', 'cover_image', 'slug'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($blog) {
            $blog->slug = Str::slug($blog->title);
        });
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }
}
