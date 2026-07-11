<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    /** @test */
    public function 自分自身のタグ名ならバリデーションを通る(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'タグテスト',
        ]);

        $request = new UpdateTagRequest;

        $route = new Route(
            'PUT',
            '/admin/tags/{tag}',
            []
        );

        $route->bind(Request::create('/admin/tags/'.$tag->id, 'PUT'));
        $route->setParameter('tag', $tag);

        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $validator = Validator::make(
            ['name' => 'タグテスト'],
            $request->rules()
        );
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 他のタグで使用されている名前はバリデーションエラーになる(): void
    {
        $existingTag = Tag::factory()->create([
            'name' => '既存タグ',
        ]);
        $tag = Tag::factory()->create([
            'name' => '更新対象タグ',
        ]);

        $request = new UpdateTagRequest;

        $route = new Route(
            'PUT',
            '/admin/tags/{tag}',
            []
        );

        $route->bind(Request::create('/admin/tags/'.$tag->id, 'PUT'));
        $route->setParameter('tag', $tag);

        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $validator = Validator::make(
            ['name' => '既存タグ'],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
    }
}
