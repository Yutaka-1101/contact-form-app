<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    /** @test */
    public function タグに紐づくお問い合わせを取得できる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $contact = Contact::factory()->count(2)->create([
            'category_id' => $category->id,
        ]);

        $tag->contacts()->sync($contact->pluck('id'));
        $this->assertCount(2, $tag->contacts);
    }
}
