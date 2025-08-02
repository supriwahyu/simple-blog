<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostDetailRequest;
use App\Http\Requests\UpdatePostDetailRequest;
use App\Models\PostDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PostDetailController extends Controller
{
    public function index()
    {
        try {

            return response()->json(['data' => PostDetail::latest()->get()]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function show($id)
    {
        try {
            $postDetail = PostDetail::where('id', $id)->orWhere('slug', $id)->firstOrFail();
            return response()->json(['data' => $postDetail]);

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
                Storage::disk('local')->put($path, file_get_contents($data['cover_image']));
            }

            $data['slug'] = Str::slug($data['title']);

            $postDetail = PostDetail::create([
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

            $found = PostDetail::find($id);

            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'excerpt' => 'nullable|string',
                'cover_image' => 'nullable|image|max:2048',
                'content' => 'required|string',
            ]);

            if ($found) {
                if (!empty($data['cover_image']) && $data['cover_image'] instanceof \Illuminate\Http\UploadedFile) {
                    $userId = Auth::user()->id;
                    $originalName = $data['cover_image']->getClientOriginalName();
                    $path = "app/images/$usersId/" . $originalName;

                    Storage::disk('local')->put($path, file_get_contents($data['cover_image']));
                }

                $data['slug'] = Str::slug($data['title']);

                $found->update([
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
                Storage::disk('local')->put($path, file_get_contents($data['cover_image']));

                $data['slug'] = Str::slug($data['title']);

                $update = PostDetail::whereNotNull('id')->updateOrCreate(
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
            $data = PostDetail::withoutTrashed()->find($id);

            if ($data == null) {
                return response()->json([
                    'error' => 'Data not found.'
                ], 404);
            }

            $deleted = $data->delete();

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
