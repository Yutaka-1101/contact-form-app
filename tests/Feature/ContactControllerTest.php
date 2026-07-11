<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    /** @test */
    public function お問い合わせフォーム入力ページが表示される(): void
    {
        Category::factory()->create([
            'content' => 'カテゴリ1',
        ]);
        Tag::factory()->create([
            'name' => 'タグ1',
        ]);
        $response = $this->get(route('contact.index'));

        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');

        $response->assertSee('カテゴリ1');
        $response->assertSee('タグ1');
    }

    /** @test */
    public function サンクスページが表示される(): void
    {
        $response = $this->get(route('contact.thanks'));

        $response->assertStatus(200);
        $response->assertViewIs('contact.thanks');
    }

    /** @test */
    public function お問い合わせ確認ページが表示される(): void
    {
        $category = Category::factory()->create();

        $response = $this->post(route('contact.confirm'), [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'マンション101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee('太郎');
        $response->assertSee('test@example.com');
    }

    /** @test */
    public function 確認ページでバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $response = $this->post(route('contact.confirm'), [
            'first_name' => '',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'マンション101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
        ]);

        $response->assertSessionHasErrors();
        $response->assertRedirect();
    }

    /** @test */
    public function お問い合わせを保存できる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->post(route('contact.store'), [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'マンション101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect(route('contact.thanks'));
        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com',
        ]);

        $contact = Contact::where('email', 'test@example.com')->first();

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    /** @test */
    public function お問い合わせ保存でバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->post(route('contact.store'), [
            'first_name' => '',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'マンション101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }
}
