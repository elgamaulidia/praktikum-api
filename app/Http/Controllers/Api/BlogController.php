<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use App\Trait\EnsureOwnerTrait;

class BlogController extends Controller
{
    use EnsureOwnerTrait;

    // BREAD
    // B = BROWSE (menampilkan list data)
    // R = READ (menampilkan detail data)
    // E = EDIT (mengubah data)
    // A = ADD (menambahkan data)
    // D = DELETE (menghapus data)

    // B (Browse): Menampilkan semua data blog
    public function browse(Request $request)
    {
        $blogs = Blog::with('user:id,name')
                    ->get()
                    ->transform(function($query) {
                        return [
                            'id' => $query->id,
                            'title' => $query->title,
                            'content' => $query->content,
                            'date' => $query->created_at?->diffForHumans(),
                            'user' => [
                                'id' => $query->user?->id,
                                'name' => $query->user?->name,
                            ],
                        ];
                    });

        return response()->json([
            'data' => $blogs
        ]);
    }

    // R (Read): Menampilkan detail blog berdasarkan ID
    public function read($id, Request $request)
    {
        $blog = Blog::findOrFail($id);
        $this->ensureOwner($request, $blog);

        return response()->json([
            'message' => "Detail blog berhasil ditampilkan",
            'data' => $blog
        ]);
    }

    // E (Edit): Mengubah data blog berdasarkan ID
    public function edit($id, Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $blog = Blog::findOrFail($id);
        $this->ensureOwner($request, $blog);
        $blog->update($data);

        return response()->json([
            'message' => "Blog berhasil diubah",
            'data' => $blog
        ]);
    }

    // A (Add): Menambahkan data blog baru
    public function add(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $blog = $request->user()->blogs()->create($data);

        return response()->json([
            'message' => "Blog berhasil ditambahkan",
            'data' => $blog
        ]);
    }

    // D (Delete): Menghapus data blog berdasarkan ID
    public function delete($id, Request $request)
    {
        $blog = Blog::findOrFail($id);
        $this->ensureOwner($request, $blog);

        $blog->delete();

        return response()->json([
            'message' => "Blog berhasil dihapus",
        ]);
    }
}
