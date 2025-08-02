<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PostDetail;

class PostController extends Controller
{
    public function index()
    {
        try {

            return response()->json(['data' => Post::with('detail')->latest()->get()]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function get()
    {
        try {

            return response()->json(['data' => Post::with('detail')->latest()->get()]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function getId(String $id)
    {
        try {
            $post = Post::with('detail')->where('id', $id)->orWhere('slug', $id)->firstOrFail();
            return response()->json(['data' => $post]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(String $id)
    {
        try {
            $post = Post::with('detail')->where('id', $id)->orWhere('slug', $id)->firstOrFail();
            return response()->json(['data' => $post]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'excerpt' => 'nullable|string',
                'cover_image' => 'nullable|image|max:2048',
                'content' => 'required|string',
            ]);

            if ($request->hasFile('cover_image')) {
                $id = Auth::user()->id;
                $originalName = $data['cover_image']->getClientOriginalName();
                $path = "app/images/$id/".$originalName;
                Storage::disk('public')->put($path, file_get_contents($data['cover_image']));
            }

            $data['slug'] = Str::slug($data['title']);

            $post = Post::create([
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'cover_image' => $path,
                'slug' => $data['slug'],
            ]);

            $postId = $post->id;

            $postDetail = PostDetail::create([
                'post_id' => $postId,
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'cover_image' => $path,
                'slug' => $data['slug'],
                'content' => $data['content'],
            ]);

            DB::commit();
            return response()->json([
                'success' => 'successfully crteated data.',
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $found = Post::find($id);
            $foundDetail = PostDetail::where('post_id', $id);

            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'excerpt' => 'nullable|string',
                'cover_image' => 'nullable',
                'content' => 'required|string',
            ]);

            if ($found) {
                if (!empty($data['cover_image']) && $data['cover_image'] instanceof \Illuminate\Http\UploadedFile) {
                    $userId = Auth::user()->id;
                    $originalName = $data['cover_image']->getClientOriginalName();
                    $path = "app/images/$userId/" . $originalName;

                    Storage::disk('public')->put($path, file_get_contents($data['cover_image']));
                }

                $data['slug'] = Str::slug($data['title']);

                $found->update([
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'cover_image' => $path ?? $found->cover_image,
                    'slug' => $data['slug'],
                ]);

                $foundDetail->update([
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'cover_image' => $path ?? $found->cover_image,
                    'slug' => $data['slug'],
                    'content' => $data['content'],
                ]);
            } else {
                $originalName = $data['cover_image']->getClientOriginalName();
                $userId = Auth::user()->id;
                $path = "app/images/$userId/".$originalName;
                Storage::disk('public')->put($path, file_get_contents($data['cover_image']));

                $data['slug'] = Str::slug($data['title']);

                $update = Post::whereNotNull('id')->updateOrCreate(
                    [
                        'id' => $found['id'],
                        'cover_image' => $path,
                    ],
                    [
                        'title' => $data['title'],
                        'excerpt' => $data['excerpt'],
                        'cover_image' => $path ?? $found->cover_image,
                        'slug' => $data['slug'],
                    ]
                );

                $updateDetail = PostDetail::whereNotNull('id')->updateOrCreate(
                    [
                        'id' => $found['id'],
                        'cover_image' => $path,
                    ],
                    [
                        'title' => $data['title'],
                        'excerpt' => $data['excerpt'],
                        'cover_image' => $path ?? $found->cover_image,
                        'slug' => $data['slug'],
                        'content' => $data['content'],
                    ]
                );

                $newId = $update->wasRecentlyCreated;
            }

            DB::commit();

            return response()->json([
                'success' => 'successfully updated data.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy(String $id)
    {
        try {
            DB::beginTransaction();
            $data = Post::find($id);
            $dataDetail = PostDetail::where('post_id', $id);

            if ($data == null) {
                return response()->json([
                    'error' => 'Data not found.'
                ], 404);
            }

            $deleted = $data->delete();
            $deletedDetail = $dataDetail->delete();

            DB::commit();

            return response()->json([
                'success' => 'successfully deleted data.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
