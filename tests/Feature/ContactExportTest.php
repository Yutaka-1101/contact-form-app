<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    /** @test */
    public function ログイン済み管理者がフィルタ条件付きで_cs_vをダウンロードできる(): void
    {
        $user = User::factory()->create();
        $categoryA = Category::factory()->create(['content' => 'Delivery']);
        $categoryB = Category::factory()->create(['content' => 'Exchange']);

        Contact::factory()->for($categoryA)->create([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'gender' => 1,
            'email' => 'john@example.com',
            'created_at' => Carbon::parse('2024-02-10 10:00:00'),
        ]);
        Contact::factory()->for($categoryB)->create([
            'first_name' => 'Alice',
            'last_name' => 'Jones',
            'gender' => 2,
            'email' => 'alice@example.com',
            'created_at' => Carbon::parse('2024-02-11 10:00:00'),
        ]);

        $response = $this->actingAs($user)->get('/contacts/export?keyword=Smith&gender=1&category_id='.$categoryA->id.'&data=2024-02-10');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('John Smith', $content);
        $this->assertStringContainsString($categoryA->content, $content);
        $this->assertStringNotContainsString('Alice Jones', $content);
    }

    /** @test */
    public function 管理者が_cs_v出力をすると、検索条件なしの場合は全件取得され、新しいお問い合わせ順で並ぶ(): void
    {
        $user = User::factory()->create();

        $older = Contact::factory()->create([
            'first_name' => 'Eve',
            'last_name' => 'Adams',
            'created_at' => Carbon::parse('2024-02-01 08:00:00'),
        ]);
        $newer = Contact::factory()->create([
            'first_name' => 'Mark',
            'last_name' => 'Brown',
            'created_at' => Carbon::parse('2024-02-02 08:00:00'),
        ]);

        $response = $this->actingAs($user)->get('/contacts/export');

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('Eve Adams', $content);
        $this->assertStringContainsString('Mark Brown', $content);

        $lines = array_values(array_filter(explode("\n", trim($content))));
        $headerLine = ltrim($lines[0] ?? '', "\xff\xBB\xBF");

        $this->assertStringContainsString('ID', $headerLine);
        $this->assertStringContainsString('Mark Brown', $lines[1] ?? '');
        $this->assertStringContainsString('Eve Adams', $lines[2] ?? '');
    }
}
