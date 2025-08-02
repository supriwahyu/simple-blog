<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\Blog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Blog::latest()->get()]);
    }

    public function show($id)
    {
        $blog = Blog::where('id', $id)->orWhere('slug', $id)->firstOrFail();
        return response()->json(['data' => $blog]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('cover_images', 'public');
        }

        $data['slug'] = Str::slug($data['title']);
        $blog = Blog::create($data);

        return response()->json(['data' => $blog], 201);
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            // delete old image
            if ($blog->cover_image) {
                Storage::disk('public')->delete($blog->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('cover_images', 'public');
        }

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $blog->update($data);
        return response()->json(['data' => $blog]);
    }

    public function destroy(Blog $blog)
    {
        if ($blog->cover_image) {
            Storage::disk('public')->delete($blog->cover_image);
        }

        $blog->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
