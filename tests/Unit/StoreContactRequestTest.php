<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Requests\StoreContactRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\Tag;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    /**
     * Summary of test_example
     * @return void
     */

    use RefreshDatabase;
    /** @test */
    public function 全ての必須項目とタグ入力が有効ならバリデーションを通る(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $request = new StoreContactRequest();

        $validator = Validator::make([
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
        ], $request->rules());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 不正な電話番号形式ならバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $request = new StoreContactRequest();

        $validator = Validator::make([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '090-1234-5678',
            'address' => '東京都',
            'building' => 'マンション101',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です。',
            'tag_ids' => [$tag->id],
        ], $request->rules());
        $this->assertTrue($validator->fails());
    }
}
