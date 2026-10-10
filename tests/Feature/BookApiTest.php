<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_books(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/books');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_book(): void
    {
        $payload = [
            'title' => 'Konsep Sistem Basis Data',
            'description' => 'Buku ini terdiri dari 17 kegiatan pembelajaran...',
            'status' => 1,
        ];

        $response = $this->postJson('/api/books', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'Konsep Sistem Basis Data',
                'status' => 1,
            ]);

        $this->assertDatabaseHas('books', [
            'title' => 'Konsep Sistem Basis Data',
        ]);
    }

    public function test_create_book_requires_title_description_and_status(): void
    {
        $response = $this->postJson('/api/books', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'status']);
    }

    public function test_can_show_book(): void
    {
        $book = Book::factory()->create([
            'title' => 'Belajar Laravel API',
        ]);

        $response = $this->getJson('/api/books/'.$book->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $book->id,
                'title' => 'Belajar Laravel API',
            ]);
    }

    public function test_show_returns_404_when_book_does_not_exist(): void
    {
        $response = $this->getJson('/api/books/9999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Book not found',
            ]);
    }

    public function test_can_update_book(): void
    {
        $book = Book::factory()->create([
            'status' => 1,
        ]);

        $response = $this->putJson('/api/books/'.$book->id, [
            'title' => $book->title,
            'description' => $book->description,
            'status' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 0,
            ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'status' => 0,
        ]);
    }

    public function test_update_returns_404_when_book_does_not_exist(): void
    {
        $response = $this->putJson('/api/books/9999', [
            'status' => 0,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Book not found',
            ]);
    }

    public function test_can_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson('/api/books/'.$book->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Book deleted',
            ]);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_delete_returns_404_when_book_does_not_exist(): void
    {
        $response = $this->deleteJson('/api/books/9999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Book not found',
            ]);
    }
}
