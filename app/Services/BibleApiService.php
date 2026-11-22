<?php

namespace App\Services;

use App\Models\DailyVerse;
use App\Models\SeedVerse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class BibleApiService
{
    private const MIN_TEXT_LENGTH = 40;

    private const MAX_API_ATTEMPTS = 5;

    /**
     * Default: EvanBible Biblia de Jerusalén (BJ).
     * Note: that host currently lists 66 books (no deuterocanonicals via API).
     */
    private function baseUrl(): string
    {
        return rtrim((string) env(
            'BIBLE_API_BASE_URL',
            'https://evanbible.com/api/v1/bj'
        ), '/');
    }

    /**
     * Pick a random verse from the Catholic Spanish Bible API (BJ),
     * falling back to curated seed verses on failure.
     *
     * @return array{text: string, reference: string, book: string, chapter: int, verse: int}
     */
    public function getRandomVerse(?string $visitorHash = null): array
    {
        try {
            $verse = $this->fetchRandomFromApi();
            if ($verse !== null) {
                return $verse;
            }
        } catch (Throwable $e) {
            Log::warning('Bible API unavailable, using seed fallback.', [
                'message' => $e->getMessage(),
            ]);
        }

        return $this->getSeedFallback($visitorHash);
    }

    /**
     * @return array{text: string, reference: string, book: string, chapter: int, verse: int}|null
     */
    private function fetchRandomFromApi(): ?array
    {
        // Prefer the API's random endpoint, then fall back to book/chapter sampling.
        for ($attempt = 0; $attempt < self::MAX_API_ATTEMPTS; $attempt++) {
            $picked = $this->fetchViaRandomEndpoint() ?? $this->fetchViaBookSampling();
            if ($picked !== null) {
                return $picked;
            }
        }

        return null;
    }

    /**
     * @return array{text: string, reference: string, book: string, chapter: int, verse: int}|null
     */
    private function fetchViaRandomEndpoint(): ?array
    {
        $payload = Http::timeout(5)
            ->acceptJson()
            ->get($this->baseUrl().'/random')
            ->throw()
            ->json();

        if (! ($payload['ok'] ?? false)) {
            return null;
        }

        $text = trim((string) ($payload['text'] ?? ''));
        if (mb_strlen($text) < self::MIN_TEXT_LENGTH) {
            return null;
        }

        $book = (string) ($payload['book_name'] ?? '');
        $chapter = (int) ($payload['chapter'] ?? 0);
        $verse = (int) ($payload['verse'] ?? 0);

        if ($book === '' || $chapter < 1 || $verse < 1) {
            return null;
        }

        return [
            'text' => $text,
            'reference' => (string) ($payload['reference'] ?? sprintf('%s %d:%d', $book, $chapter, $verse)),
            'book' => $book,
            'chapter' => $chapter,
            'verse' => $verse,
        ];
    }

    /**
     * @return array{text: string, reference: string, book: string, chapter: int, verse: int}|null
     */
    private function fetchViaBookSampling(): ?array
    {
        $booksPayload = Http::timeout(5)
            ->acceptJson()
            ->get($this->baseUrl().'/books')
            ->throw()
            ->json();

        $books = $booksPayload['books'] ?? null;
        if (! is_array($books) || $books === []) {
            return null;
        }

        $book = $books[array_rand($books)];
        $bookId = (int) ($book['id'] ?? 0);
        $bookName = (string) ($book['name'] ?? '');
        if ($bookId < 1 || $bookName === '') {
            return null;
        }

        $meta = Http::timeout(5)
            ->acceptJson()
            ->get($this->baseUrl().'/'.$bookId)
            ->throw()
            ->json();

        $chapters = (int) ($meta['chapters'] ?? 0);
        if ($chapters < 1) {
            return null;
        }

        $chapter = random_int(1, $chapters);

        $chapterPayload = Http::timeout(5)
            ->acceptJson()
            ->get($this->baseUrl()."/{$bookId}/{$chapter}")
            ->throw()
            ->json();

        $verses = $chapterPayload['verses'] ?? null;
        if (! is_array($verses) || $verses === []) {
            return null;
        }

        $eligible = array_values(array_filter($verses, function ($item) {
            $text = trim((string) ($item['text'] ?? ''));

            return mb_strlen($text) >= self::MIN_TEXT_LENGTH;
        }));

        if ($eligible === []) {
            return null;
        }

        $picked = $eligible[array_rand($eligible)];
        $text = trim((string) $picked['text']);
        $verseNumber = (int) ($picked['verse'] ?? 0);
        if ($verseNumber < 1) {
            return null;
        }

        return [
            'text' => $text,
            'reference' => sprintf('%s %d:%d', $bookName, $chapter, $verseNumber),
            'book' => $bookName,
            'chapter' => $chapter,
            'verse' => $verseNumber,
        ];
    }

    /**
     * @return array{text: string, reference: string, book: string, chapter: int, verse: int}
     */
    private function getSeedFallback(?string $visitorHash): array
    {
        $query = SeedVerse::query();

        if ($visitorHash) {
            $recentReferences = DailyVerse::query()
                ->where('visitor_hash', $visitorHash)
                ->whereDate('shown_at', '>=', now()->subDays(30)->toDateString())
                ->pluck('verse_reference');

            if ($recentReferences->isNotEmpty()) {
                $query->whereNotIn('reference', $recentReferences);
            }
        }

        $seed = $query->inRandomOrder()->first() ?? SeedVerse::query()->inRandomOrder()->first();

        if (! $seed) {
            return [
                'text' => 'Porque tanto amó Dios al mundo que dio a su Hijo único, para que todo el que crea en él no perezca, sino que tenga vida eterna.',
                'reference' => 'Juan 3:16',
                'book' => 'Juan',
                'chapter' => 3,
                'verse' => 16,
            ];
        }

        return [
            'text' => $seed->text,
            'reference' => $seed->reference,
            'book' => $seed->book,
            'chapter' => (int) $seed->chapter,
            'verse' => (int) $seed->verse,
        ];
    }
}
