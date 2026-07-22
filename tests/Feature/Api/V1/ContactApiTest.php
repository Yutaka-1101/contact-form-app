<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    // ================= INDEX =================

    /** @test */
    public function お問い合わせ一覧_ap_iが_json形式で返る(): void
    {
        Contact::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'category' => ['id', 'content'],
                    'first_name',
                    'last_name',
                    'gender',
                    'email',
                    'tel',
                    'address',
                    'building',
                    'detail',
                    'tags',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
        $response->assertJsonPath('meta.total', 3);
    }

    /** @test */
    public function お問い合わせ一覧_ap_iがデフォルトで20件ごとにページネーションされる(): void
    {
        Contact::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk();
        $response->assertJsonPath('meta.per_page', 20);
        $response->assertJsonCount(20, 'data');
    }

    /** @test */
    public function お問い合わせ一覧_ap_iが指定の件数でページネーションできる(): void
    {
        Contact::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/contacts?per_page=5');

        $response->assertOk();
        $response->assertJsonPath('meta.per_page', 5);
        $response->assertJsonCount(5, 'data');
    }

    /** @test */
    public function キーワードで絞り込みができる(): void
    {
        Contact::factory()->create(['first_name' => 'Ken', 'email' => 'ken@example.com']);
        Contact::factory()->create(['first_name' => 'Jane', 'email' => 'jane@example.com']);

        $response = $this->getJson('/api/v1/contacts?keyword=Ken');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.first_name', 'Ken');
    }

    /** @test */
    public function 性別で絞り込みができる(): void
    {
        Contact::factory()->create(['gender' => 1]);
        Contact::factory()->create(['gender' => 2]);

        $response = $this->getJson('/api/v1/contacts?gender=1');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.gender', 1);
    }

    /** @test */
    public function カテゴリー_i_dで絞り込みができる(): void
    {
        $category = Category::factory()->create();
        Contact::factory()->for($category)->create();
        Contact::factory()->create();

        $response = $this->getJson('/api/v1/contacts?category_id='.$category->id);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function 日付で絞り込みができる(): void
    {
        Contact::factory()->create(['created_at' => Carbon::parse('2024-02-01 09:00:00')]);
        Contact::factory()->create(['created_at' => Carbon::parse('2024-02-02 09:00:00')]);

        $response = $this->getJson('/api/v1/contacts?date=2024-02-01');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function 不正な性別値を入れるとバリデーションエラーになる(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=9');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('gender');
    }

    // ================= SHOW =================

    /** @test */
    public function お問い合わせ内容の詳細が確認できる(): void
    {
        $category = Category::factory()->create(['content' => 'Support']);
        $tag = Tag::factory()->create(['name' => '質問']);
        $contact = Contact::factory()->for($category)->create([
            'first_name' => 'Mika',
            'last_name' => 'Suzuki',
        ]);
        $contact->tags()->attach($tag);

        $response = $this->getJson('/api/v1/contacts/'.$contact->id);

        $response->assertOk();
        $response->assertJsonPath('data.first_name', 'Mika');
        $response->assertJsonPath('data.last_name', 'Suzuki');
        $response->assertJsonPath('data.category.content', 'Support');
        $response->assertJsonPath('data.tags.0.name', '質問');
    }

    /** @test */
    public function お問い合わせが見つからない場合は404を返す(): void
    {
        $response = $this->getJson('/api/v1/contacts/9999');

        $response->assertNotFound();
        $response->assertJson(['error' => 'お問い合わせが見つかりませんでした。']);
    }

    // ================= STORE =================

    /** @test */
    public function お問い合わせが作成できた時に201を返す(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $payload = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => '渋谷ビル301',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => $tags->pluck('id')->toArray(),
        ];

        $response = $this->postJson('/api/v1/contacts', $payload);

        $response->assertCreated();
        $response->assertJsonPath('data.first_name', '山田');
        $response->assertJsonPath('data.email', 'yamada@example.com');
        $response->assertJsonPath('data.category.id', $category->id);
        $response->assertJsonCount(2, 'data.tags');

        $this->assertDatabaseHas('contacts', ['email' => 'yamada@example.com']);
        $contact = Contact::where('email', 'yamada@example.com')->first();
        foreach ($tags as $tag) {
            $this->assertDatabaseHas('contact_tag', [
                'contact_id' => $contact->id,
                'tag_id' => $tag->id,
            ]);
        }
    }

    /** @test */
    public function 必須項目が空の場合はバリデーションエラーになる(): void
    {
        $response = $this->postJson('/api/v1/contacts', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    /** @test */
    public function 不正なメール形式の場合はバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();
        $payload = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'invalid-email',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ];

        $response = $this->postJson('/api/v1/contacts', $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
    }

    // ================= UPDATE =================
    /** @test */
    public function お問い合わせを更新し、_jso_nを返す(): void
    {
        $category = Category::factory()->create();
        $newCategory = Category::factory()->create(['content' => '新しいカテゴリ']);
        $contact = Contact::factory()->for($category)->create([
            'first_name' => '田中',
            'last_name' => '花子',
        ]);
        $newTag = Tag::factory()->create(['name' => '更新済み']);

        $payload = [
            'first_name' => '佐藤',
            'last_name' => '次郎',
            'gender' => 2,
            'email' => 'sato@example.com',
            'tel' => '08011112222',
            'address' => '大阪府大阪市1-2-3',
            'category_id' => $newCategory->id,
            'detail' => '更新内容です',
            'tag_ids' => [$newTag->id],
        ];

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $payload);

        $response->assertOk();
        $response->assertJsonPath('data.first_name', '佐藤');
        $response->assertJsonPath('data.last_name', '次郎');
        $response->assertJsonPath('data.category.content', '新しいカテゴリ');
        $response->assertJsonCount(1, 'data.tags');
        $response->assertJsonPath('data.tags.0.name', '更新済み');

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '佐藤',
        ]);
    }

    /** @test */
    public function 存在しないお問い合わせを更新しようとすると404が返る(): void
    {
        $category = Category::factory()->create();

        $payload = [
            'first_name' => '佐藤',
            'last_name' => '次郎',
            'gender' => 2,
            'email' => 'sato@example.com',
            'tel' => '08011112222',
            'address' => '大阪府',
            'category_id' => $category->id,
            'detail' => '内容',
        ];

        $response = $this->putJson('/api/v1/contacts/9999', $payload);

        $response->assertNotFound();
    }

    /** @test */
    public function 不正なデータで更新した場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['first_name', 'last_name']);
    }

    // ================= DESTROY =================
    /** @test */
    public function お問い合わせを削除した場合、204を返しデータベースから削除される(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->deleteJson('/api/v1/contacts/'.$contact->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    /** @test */
    public function 存在しないお問い合わせを削除しようとすると404を返す(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/9999');

        $response->assertNotFound();
    }
}
