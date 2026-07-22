<?php

namespace Tests\Unit\Requests\Api\V1;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    private function validator(array $data)
    {
        $request = new IndexContactRequest;

        return Validator::make($data, $request->rules(), $request->messages());
    }

    /** @test */
    public function 正しい検索条件を渡した場合、バリデーションを通過する(): void
    {
        $category = Category::factory()->create();

        $validator = $this->validator([
            'keyword' => 'Yamada',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2024-02-01',
            'oer_page' => 50,
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 検索条件が空でもバリデーションを通過する(): void
    {
        $validator = $this->validator([]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 性別に許可されていない値を入れた場合はバリデーションエラーになる(): void
    {
        $validator = $this->validator([
            'gender' => 0,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->messages());
    }

    /** @test */
    public function 不正な性別値はバリデーションエラーになる(): void
    {
        $validator = $this->validator([
            'gender' => 9,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->messages());
    }

    /** @test */
    public function 存在しないカテゴリー_i_dを入力した場合はバリデーションエラーになる(): void
    {
        $validator = $this->validator([
            'category_id' => 9999,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->messages());
    }

    /** @test */
    public function 不正な日付形式はバリデーションエラーになる(): void
    {
        $validator = $this->validator([
            'date' => 'not-a-date',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date', $validator->errors()->messages());
    }

    /** @test */
    public function ページあたりの取得件数が100件を超える場合はエラーになる(): void
    {
        $validator = $this->validator([
            'per_page' => 101,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->messages());
    }

    /** @test */
    public function ページあたりの取得件数が0件の場合はエラーになる(): void
    {
        $validator = $this->validator([
            'per_page' => 0,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->messages());
    }
}
