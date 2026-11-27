<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UMBRAL · Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --umbral: #9c8974; }
        .umbral-bg   { background-color: var(--umbral); }
        .umbral-text { color: var(--umbral); }
        .umbral-border { border-color: var(--umbral); }
    </style>
</head>
<body class="bg-stone-100 min-h-screen font-sans">

    {{-- Header --}}
    <header class="umbral-bg text-white py-4 px-6 shadow">
        <div class="max-w-4xl mx-auto flex items-center gap-3">
            <span class="text-2xl font-bold tracking-widest">UMBRAL</span>
            <span class="text-stone-200 text-sm">· Panel de estadísticas</span>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8 space-y-8">

        {{-- Tarjetas resumen --}}
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow p-6 text-center border-t-4 umbral-border">
                <p class="text-5xl font-bold umbral-text">{{ number_format($totalHoy) }}</p>
                <p class="text-stone-500 mt-2 text-sm uppercase tracking-wide">Hoy</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center border-t-4 umbral-border">
                <p class="text-5xl font-bold umbral-text">{{ number_format($totalMes) }}</p>
                <p class="text-stone-500 mt-2 text-sm uppercase tracking-wide">Este mes</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center border-t-4 umbral-border">
                <p class="text-5xl font-bold umbral-text">{{ number_format($totalHistorico) }}</p>
                <p class="text-stone-500 mt-2 text-sm uppercase tracking-wide">Total histórico</p>
            </div>
        </section>

        {{-- Tabla últimos 30 días --}}
        <section class="bg-white rounded-xl shadow overflow-hidden">
            <div class="umbral-bg text-white px-6 py-3 font-semibold text-sm uppercase tracking-wide">
                Visitas por día — últimos 30 días
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                        <tr>
                            <th class="text-left px-6 py-3">Fecha</th>
                            <th class="text-right px-6 py-3">Visitantes únicos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($ultimos30 as $fila)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="px-6 py-3">
                                {{ \Carbon\Carbon::parse($fila->dia)->locale('es')->isoFormat('dddd D MMM YYYY') }}
                                @if($fila->dia === $today)
                                    <span class="ml-2 text-xs umbral-text font-semibold">(hoy)</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-mono font-semibold">
                                {{ number_format($fila->visitas) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-6 text-center text-stone-400">
                                Sin datos aún
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Top versículos --}}
        <section class="bg-white rounded-xl shadow overflow-hidden">
            <div class="umbral-bg text-white px-6 py-3 font-semibold text-sm uppercase tracking-wide">
                Top 10 versículos más mostrados
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                        <tr>
                            <th class="text-left px-6 py-3">#</th>
                            <th class="text-left px-6 py-3">Referencia</th>
                            <th class="text-right px-6 py-3">Veces mostrado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($topVersos as $i => $v)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="px-6 py-3 text-stone-400">{{ $i + 1 }}</td>
                            <td class="px-6 py-3 font-medium umbral-text">{{ $v->verse_reference }}</td>
                            <td class="px-6 py-3 text-right font-mono font-semibold">{{ $v->veces }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-6 text-center text-stone-400">Sin datos aún</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <p class="text-center text-stone-400 text-xs">
            Datos al {{ now(config('app.timezone'))->isoFormat('D MMM YYYY, HH:mm') }} hora México
        </p>

    </main>
</body>
</html>
