
<?php

use Illuminate\Support\Facades\Route;
use App\Models\Blog;
use App\Http\Controllers\Api\AuthController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('profile', [AuthController::class, 'profile']);

    // BREAD
    // B = BROWSE
    // R = READ
    // E = EDIT
    // A = ADD
    // D = DELETE

    // B (Browse): Menampilkan semua data blog
    Route::get('/blogs', function() {
        $blogs = Blog::get();

        return response()->json([
            'data' => $blogs
        ]);
    });

    // R (Read): Menampilkan detail blog
    Route::get('/blog/{id}', function($id) {
        $blog = Blog::findOrFail($id);

        return response()->json([
            'message' => "Detail blog berhasil ditampilkan",
            'data' => $blog
        ]);
    });

    // E (Edit): Mengubah data blog
    Route::put('/blog/{id}', function($id) {
        $blog = Blog::findOrFail($id);

        $blog->update([
            'title' => request()->get('title'),
            'content' => request()->get('content'),
        ]);

        return response()->json([
            'message' => "Blog berhasil diubah",
        ]);
    });

    // A (Add): Menambahkan blog baru
    Route::post('/blogs', function() {
        $blog = Blog::create([
            'title' => request()->get('title'),
            'content' => request()->get('content'),
        ]);

        return response()->json([
            'message' => "Blog berhasil ditambahkan",
            'data' => $blog
        ]);
    });

    // D (Delete): Menghapus blog
    Route::delete('/blog/{id}', function($id) {
        $blog = Blog::findOrFail($id);

        $blog->delete();

        return response()->json([
            'message' => "Blog berhasil dihapus",
        ]);
    });
});
