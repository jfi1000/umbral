<?php

namespace App\Http\Controllers;

use App\Models\DailyVerse;
use App\Services\BibleApiService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerseController extends Controller
{
    public function __construct(private readonly BibleApiService $bibleApi)
    {
    }

    public function index(): View
    {
        return view('welcome');
    }

    public function verse(Request $request): JsonResponse
    {
        $today = now()->toDateString();
        $visitorHash = $this->visitorHash($request, $today);

        $existing = DailyVerse::query()
            ->where('visitor_hash', $visitorHash)
            ->whereDate('shown_at', $today)
            ->first();

        if ($existing) {
            return response()->json($this->formatPayload($existing, $today));
        }

        $picked = $this->bibleApi->getRandomVerse($visitorHash);

        try {
            $record = DailyVerse::query()->create([
                'visitor_hash' => $visitorHash,
                'verse_reference' => $picked['reference'],
                'verse_text' => $picked['text'],
                'verse_book' => $picked['book'],
                'verse_chapter' => $picked['chapter'],
                'verse_number' => $picked['verse'],
                'shown_at' => $today,
                'created_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            $record = DailyVerse::query()
                ->where('visitor_hash', $visitorHash)
                ->whereDate('shown_at', $today)
                ->firstOrFail();
        }

        return response()->json($this->formatPayload($record, $today));
    }

    private function visitorHash(Request $request, string $date): string
    {
        $forwarded = $request->header('X-Forwarded-For');
        $ip = $forwarded
            ? trim(explode(',', $forwarded)[0])
            : (string) $request->ip();

        $userAgent = (string) $request->userAgent();

        return hash('sha256', $ip.$userAgent.$date);
    }

    /**
     * @return array{text: string, reference: string, book: string, chapter: int, verse: int, date: string}
     */
    private function formatPayload(DailyVerse $verse, string $date): array
    {
        return [
            'text' => $verse->verse_text,
            'reference' => $verse->verse_reference,
            'book' => $verse->verse_book,
            'chapter' => (int) $verse->verse_chapter,
            'verse' => (int) $verse->verse_number,
            'date' => $date,
        ];
    }
}
