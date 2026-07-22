<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    private function makeValidator(array $data)
    {
        $request = new ExportContactRequest;

        return Validator::make($data, $request->rules());
    }

    /** @test */
    public function 正しい検索条件ならバリデーションを通過する(): void
    {
        $category = Category::factory()->create();

        $validator = $this->makeValidator([
            'keyword' => 'delivery',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-07-07',
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 性別が不正な値ならバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $validator = $this->makeValidator([
            'gender' => 5,
            'category_id' => $category->id,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->messages());
    }

    /** @test */
    public function 存在しないカテゴリー_i_dならバリデーションエラーになる(): void
    {
        $category = Category::factory()->create();

        $validator = $this->makeValidator([
            'category_id' => 999,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->messages());
    }
}
