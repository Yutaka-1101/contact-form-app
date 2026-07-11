<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーはタグ編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->get(route('tags.edit', $tag));

        $response->assertStatus(200);
        $response->assertViewIs('admin.tags.edit');
    }

    /** @test */
    public function 認証済みユーザーはタグを作成できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tags.store'), [
            'name' => 'テストタグ',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function 認証済みユーザーはタグを更新できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create([
            'name' => 'テストタグ',
        ]);

        $response = $this->actingAs($user)->put(route('tags.update', $tag), [
            'name' => '更新タグ',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('tags', [
            'name' => '更新タグ',
        ]);
    }

    /** @test */
    public function 認証済みユーザーはタグを削除できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    /** @test */
    public function 未認証はタグ編集画面へログインするとリダイレクトされる(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.edit', $tag));

        $response->assertRedirect(route('login'));
    }
}
