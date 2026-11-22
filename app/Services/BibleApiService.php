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

    private function baseUrl(): string
    {
        return rtrim((string) env(
            'BIBLE_API_BASE_URL',
            'https://ajphchgh0i.execute-api.us-west-2.amazonaws.com/dev/api'
        ), '/');
    }

    /**
     * Pick a random verse from the Spanish Bible API (RV1960),
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
        $books = Http::timeout(5)
            ->acceptJson()
            ->get($this->baseUrl().'/books')
            ->throw()
            ->json();

        if (! is_array($books) || $books === []) {
            return null;
        }

        for ($attempt = 0; $attempt < self::MAX_API_ATTEMPTS; $attempt++) {
            $book = $books[array_rand($books)];
            $chapters = $book['chapters'] ?? [];

            if (! is_array($chapters) || $chapters === []) {
                continue;
            }

            $chapterMeta = $chapters[array_rand($chapters)];
            $chapter = (int) ($chapterMeta['chapter'] ?? 0);
            $bookSlug = $this->bookSlug($book['id'] ?? '');

            if ($bookSlug === '' || $chapter < 1) {
                continue;
            }

            $verses = Http::timeout(5)
                ->acceptJson()
                ->get($this->baseUrl()."/books/{$bookSlug}/chapters/{$chapter}/verses")
                ->throw()
                ->json();

            if (! is_array($verses) || $verses === []) {
                continue;
            }

            $eligible = array_values(array_filter($verses, function ($item) {
                $text = $this->extractVerseText($item);

                return mb_strlen(trim($text)) >= self::MIN_TEXT_LENGTH;
            }));

            if ($eligible === []) {
                continue;
            }

            $picked = $eligible[array_rand($eligible)];
            $text = trim($this->extractVerseText($picked));
            $verseNumber = (int) ($picked['verse'] ?? $picked['number'] ?? $picked['verse_number'] ?? 0);
            $bookName = (string) ($book['name'] ?? $bookSlug);

            if ($verseNumber < 1 || $text === '') {
                continue;
            }

            return [
                'text' => $text,
                'reference' => sprintf('%s %d:%d', $bookName, $chapter, $verseNumber),
                'book' => $bookName,
                'chapter' => $chapter,
                'verse' => $verseNumber,
            ];
        }

        return null;
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
                'text' => 'Porque de tal manera amó Dios al mundo, que ha dado a su Hijo unigénito, para que todo aquel que en él cree, no se pierda, mas tenga vida eterna.',
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

    private function bookSlug(string $id): string
    {
        if (str_contains($id, ':')) {
            return substr($id, strrpos($id, ':') + 1);
        }

        return $id;
    }

    private function extractVerseText(mixed $item): string
    {
        if (! is_array($item)) {
            return is_string($item) ? $item : '';
        }

        foreach (['text', 'verse_text', 'content', 'value'] as $key) {
            if (isset($item[$key]) && is_string($item[$key])) {
                return $item[$key];
            }
        }

        return '';
    }
}
