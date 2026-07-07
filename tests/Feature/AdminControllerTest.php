<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーは管理画面を表示できる(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
    }

    /** @test */
    public function 未認証ユーザーはログインへリダイレクトされる(): void
    {
        $response = $this->get(route('admin.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function キーワードでお問い合わせを検索できる(): void
    {
        $user = User::factory()->create();
        Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
        ]);
        Contact::factory()->create([
            'first_name' => '次郎',
            'last_name' => '田中',
            'email' => 'jiro@example.com',
        ]);
        $response = $this->actingAs($user)->get(route('admin.index', [
            'keyword' => '太郎',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertSee('太郎');
        $response->assertDontSee('次郎');
    }

    /** @test */
    public function 性別でお問い合わせを検索できる(): void
    {
        $user = User::factory()->create();
        Contact::factory()->create([
            'first_name' => '男性ユーザー',
            'gender' => 1,
        ]);
        Contact::factory()->create([
            'first_name' => '女性ユーザー',
            'gender' => 2,
        ]);
        $response = $this->actingAs($user)->get(route('admin.index', [
            'gender' => 1,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertSee('男性ユーザー');
        $response->assertDontSee('女性ユーザー');
    }

    /** @test */
    public function カテゴリでお問い合わせを検索できる(): void
    {
        $user = User::factory()->create();
        $category1 = Category::factory()->create([
            'content' => 'カテゴリA'
        ]);
        $category2 = Category::factory()->create([
            'content' => 'カテゴリB'
        ]);

        Contact::factory()->create([
            'first_name' => 'カテゴリAのお問い合わせ',
            'category_id' => $category1->id,
        ]);
        Contact::factory()->create([
            'first_name' => 'カテゴリBのお問い合わせ',
            'category_id' => $category2->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.index', [
            'category_id' => $category1->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertSee('カテゴリAのお問い合わせ');
        $response->assertDontSee('カテゴリBのお問い合わせ');
    }

    /** @test */
    public function 日付でお問い合わせを検索できる(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create([
            'first_name' => '今日のお問い合わせ',
            'created_at' => Carbon::parse('2026-07-07'),
        ]);
        Contact::factory()->create([
            'first_name' => '昨日のお問い合わせ',
            'created_at' => Carbon::parse('2026-07-06'),
        ]);

        $response = $this->actingAs($user)->get(route('admin.index', [
            'date' => '2026-07-07',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
        $response->assertSee('今日のお問い合わせ');
        $response->assertDontSee('昨日のお問い合わせ');
    }

    /** @test */
    public function お問い合わせ一覧は7件ごとにページネーションされる(): void
    {
        $user = User::factory()->create();
        Contact::factory()->count(8)->create();

        $response = $this->actingAs($user)->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');

        $contacts = $response->viewData('contacts');

        $this->assertCount(7, $contacts->items());
        $this->assertTrue($contacts->hasPages());
    }

    /** @test */
    public function お問い合わせ詳細を表示できる(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.show', $contact));

        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertSee($contact->first_name);
        $response->assertSee($contact->category->content);
    }

    /** @test */
    public function お問い合わせを削除できる(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.destroy', $contact));

        $response->assertRedirect(route('admin.index'));
    }
}
