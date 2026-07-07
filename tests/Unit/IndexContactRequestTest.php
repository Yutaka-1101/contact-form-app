<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Requests\IndexContactRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 有効なキーワードならバリデーションを通る(): void
    {
        $request = new IndexContactRequest();
        $validator = Validator::make(['keyword' => '山田'], $request->rules());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 男性ならバリデーションを通る(): void
    {
        $request = new IndexContactRequest();
        $validator = Validator::make(['gender' => 1], $request->rules());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function ジェンダーに４を入れたらバリデーションエラーになる(): void
    {
        $request = new IndexContactRequest();
        $validator = Validator::make(['gender' => 4], $request->rules());
        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 存在するカテゴリーIDならバリデーションを通る(): void
    {
        $category = Category::factory()->create();
        $request = new IndexContactRequest();
        $validator = Validator::make(['category_id' => $category->id], $request->rules());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 正しい日付ならバリデーションを通る(): void
    {
        $request = new IndexContactRequest();
        $validator = Validator::make(['date' => '2026-07-07'], $request->rules());
        $this->assertFalse($validator->fails());
    }
}
