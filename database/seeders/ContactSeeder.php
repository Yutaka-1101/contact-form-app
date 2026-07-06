<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use Faker\Factory as Faker;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $tags = Tag::all();

        Contact::factory(20)
            ->make()
            ->each(function ($contact) use ($categories, $tags) {
                $contact->category_id = $categories->random()->id;
                $contact->save();

                $contact->tags()->attach(
                    $tags->random(rand(1, 3))->pluck('id')->toArray()
                );
            });
    }
}
