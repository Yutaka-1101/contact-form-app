<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    /** @test */
    public function タグ名を入力していたらバリデーションを通る(): void
    {
        $request = new StoreTagRequest;
        $validator = Validator::make(['name' => 'タグテスト'], $request->rules());
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function タグ名が空ならバリデーションエラーになる(): void
    {
        $request = new StoreTagRequest;
        $validator = Validator::make(['name' => ''], $request->rules());
        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function タグ名が51文字以上ならバリデーションエラーになる(): void
    {
        $request = new StoreTagRequest;
        $validator = Validator::make(['name' => str_repeat('あ', 51)], $request->rules());
        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 既に存在しているタグ名はバリデーションエラーになる(): void
    {
        $tag = Tag::factory()->create();
        $request = new StoreTagRequest;
        $validator = Validator::make(['name' => $tag->name], $request->rules());
        $this->assertTrue($validator->fails());
    }
}
