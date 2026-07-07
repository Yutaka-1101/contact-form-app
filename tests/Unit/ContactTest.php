<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    /** @test */
    public function お問い合わせはカテゴリーと複数タグを取得できる(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->sync($tags->pluck('id'));

        $this->assertEquals($category->id, $contact->category->id);
        $this->assertCount(2, $contact->tags);
    }
}
