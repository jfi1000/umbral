<?php
namespace App\Http\Controllers;

use App\Models\DailyVerse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(): View
    {
        $tz   = config('app.timezone', 'America/Mexico_City');
        $today = now($tz)->toDateString();
        $thisMonth = now($tz)->format('Y-m');

        // Visitantes únicos (cada fila = 1 visita única por día por visitante)
        $totalHistorico = DailyVerse::count();

        $totalHoy = DailyVerse::whereDate('shown_at', $today)->count();

        $totalMes = DailyVerse::whereRaw("DATE_FORMAT(shown_at, '%Y-%m') = ?", [$thisMonth])->count();

        // Últimos 30 días: visitas por día (para la tabla)
        $ultimos30 = DailyVerse::selectRaw("DATE(shown_at) as dia, COUNT(*) as visitas")
            ->where('shown_at', '>=', now($tz)->subDays(29)->toDateString())
            ->groupBy('dia')
            ->orderBy('dia', 'desc')
            ->get();

        // Top 10 versículos más mostrados
        $topVersos = DailyVerse::selectRaw("verse_reference, COUNT(*) as veces")
            ->groupBy('verse_reference')
            ->orderByDesc('veces')
            ->limit(10)
            ->get();

        return view('admin.index', compact(
            'totalHistorico', 'totalHoy', 'totalMes',
            'ultimos30', 'topVersos', 'today'
        ));
    }
}
