<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    /** @test */
    public function カテゴリーに紐づくお問い合わせを取得できる(): void
    {
        $category = Category::factory()->create();
        Contact::factory()->count(2)->create([
            'category_id' => $category->id,
        ]);
        $this->assertCount(2, $category->contacts);
    }
}
