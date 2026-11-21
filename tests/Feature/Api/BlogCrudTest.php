<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

uses(LazilyRefreshDatabase::class);

it('create dan simpan blog testing', function () {
    Sanctum::actingAs(User::factory()->create());
    $payload = ['title' => 'Catatan baru', 'content' => 'Isi blog'];

    $response = $this->postJson('/api/blogs', $payload)
        ->assertOk()
        ->assertJsonPath('data.title', $payload['title']);

    $this->assertDatabaseHas('blogs', [
        'id' => $response->json('data.id'),
        ...$payload,
    ]);
});

it('list atau menampilkan daftar blog kepada pengguna yang punya izin membaca', function () {
    $owner = User::factory()->create();
    $blog = $owner->blogs()->create(['title' => 'Catatan', 'content' => 'Isi blog']);
    Sanctum::actingAs($owner, ['blogs:read']);

    $this->getJson('/api/blogs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $blog->id)
        ->assertJsonPath('data.0.user.id', $owner->id);
});

it('menampilkan detail blog kepada pengguna yang punya izin membaca', function () {
    $owner = User::factory()->create();
    $blog = $owner->blogs()->create(['title' => 'Catatan pribadi']);
    Sanctum::actingAs($owner, ['blogs:read']);

    $this->getJson("/api/blog/{$blog->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $blog->id)
        ->assertJsonPath('data.user_id', $owner->id);
});


it('ubah judul dan isi blog dari milik pribadi', function () {
    $owner = User::factory()->create();
    $blog = $owner->blogs()->create(['title' => 'Judul awal', 'content' => 'Isi awal']);
    Sanctum::actingAs($owner, ['blogs:update']);
    $payload = ['title' => 'Judul baru', 'content' => 'Isi baru'];

    $this->putJson("/api/blog/{$blog->id}", $payload)->assertOk();

    $this->assertDatabaseHas('blogs', [
        'id' => $blog->id,
        'user_id' => $owner->id,
        ...$payload,
    ]);
});

it('hapus blog dari milik pribadi dengan izin hapus', function () {
    $owner = User::factory()->create();
    $blog = $owner->blogs()->create(['title' => 'Catatan lama']);
    Sanctum::actingAs($owner, ['blogs:delete']);

    $this->deleteJson("/api/blog/{$blog->id}")->assertOk();

    $this->assertModelMissing($blog);
});