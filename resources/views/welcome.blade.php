<!DOCTYPE html>
<html lang="es" x-data="umbralApp()" x-init="init()" :class="{ 'dark': theme === 'dark' }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="UMBRAL — Un verso que te acompaña hoy">
    <title>UMBRAL</title>

    {{-- Editorial demo typography: Playfair Display + Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Playfair+Display:ital,wght@0,400;0,500;1,400&display=swap"
        rel="stylesheet"
    >

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        editorial: ['"Playfair Display"', 'Georgia', 'serif'],
                        'sans-clean': ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        brand: '#9c8974',
                    },
                },
            },
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- AFTER Tailwind CDN so preflight cannot wipe font-family --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Playfair+Display:ital,wght@0,400;0,500;1,400&display=swap');

        :root {
            --font-editorial: 'Playfair Display', Georgia, serif;
            --font-sans-clean: 'Inter', system-ui, -apple-system, sans-serif;
            --bg-gradient: radial-gradient(circle at top, #fdfbf7 0%, #f3eee5 100%);
            --text-primary: #1f1c19;
            --text-secondary: #6e6357;
            --text-muted: #8c7f70;
            --divider-color: #c5b8a5;
            --btn-bg: rgba(140, 127, 112, 0.08);
            --btn-hover: rgba(140, 127, 112, 0.16);
            --btn-border: rgba(140, 127, 112, 0.2);
        }

        /* Match demo-editorial.html dark tokens exactly (html.dark) */
        html.dark {
            --bg-gradient: radial-gradient(circle at top, #1a1917 0%, #11100f 100%);
            --text-primary: #f2ede4;
            --text-secondary: #a89f91;
            --text-muted: #7d7468;
            --divider-color: #3d3832;
            --btn-bg: rgba(242, 237, 228, 0.06);
            --btn-hover: rgba(242, 237, 228, 0.12);
            --btn-border: rgba(242, 237, 228, 0.15);
        }

        html, body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg-gradient);
            color: var(--text-primary);
            font-family: var(--font-sans-clean) !important;
            transition: background 0.5s ease, color 0.5s ease;
        }

        .font-editorial {
            font-family: var(--font-editorial) !important;
        }

        .font-sans-clean {
            font-family: var(--font-sans-clean) !important;
        }

        .brand-mark {
            letter-spacing: 0.28em;
            color: #9c8974;
            font-size: 0.95rem;
        }

        .verse-text {
            font-size: clamp(1.85rem, 4.5vw, 3.35rem);
            line-height: 1.38;
            font-weight: 500;
            color: var(--text-primary);
            letter-spacing: -0.01em;
        }

        .meta-label {
            text-transform: uppercase;
            letter-spacing: 0.11em;
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 500;
        }

        .divider {
            width: 56px;
            height: 1px;
            background: var(--divider-color);
            margin: 1.75rem auto;
        }

        .reference {
            font-size: clamp(0.875rem, 1.5vw, 1rem);
            letter-spacing: 0.08em;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 500;
        }

        .theme-btn {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 40;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.95rem;
            border-radius: 9999px;
            border: 1px solid var(--btn-border);
            background: var(--btn-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: var(--text-secondary);
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease;
        }

        .theme-btn:hover {
            background: var(--btn-hover);
        }

        @keyframes fadeUpSlow {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-up {
            animation: fadeUpSlow 1.3s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .fade-up-delay {
            animation: fadeUpSlow 1.3s cubic-bezier(0.22, 1, 0.36, 1) 0.18s both;
        }

        .skeleton {
            opacity: 0.45;
        }
    </style>
</head>
<body>
    <button
        type="button"
        class="theme-btn font-sans-clean"
        @click="toggleTheme()"
        :aria-label="theme === 'dark' ? 'Cambiar a tema papel' : 'Cambiar a tema noche'"
    >
        <span x-show="theme === 'dark'" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path>
            </svg>
        </span>
        <span x-show="theme !== 'dark'" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M21 14.5A8.5 8.5 0 0 1 9.5 3 7 7 0 1 0 21 14.5z"></path>
            </svg>
        </span>
        <span x-text="theme === 'dark' ? 'Papel' : 'Noche'"></span>
    </button>

    <main class="min-h-screen flex flex-col items-center justify-center px-6 py-20">
        <div class="w-full max-w-3xl text-center">
            <p
                class="brand-mark font-editorial fade-up mb-10"
                style="font-family: 'Playfair Display', Georgia, serif;"
            >UMBRAL</p>

            <p class="meta-label font-sans-clean fade-up mb-8">Un verso que te acompaña hoy</p>

            <div class="fade-up-delay min-h-[8rem] flex items-center justify-center">
                <p
                    class="verse-text font-editorial"
                    style="font-family: 'Playfair Display', Georgia, serif;"
                    :class="{ 'skeleton': loading }"
                    x-text="loading ? '…' : (verse?.text || '')"
                ></p>
            </div>

            <div class="divider fade-up-delay"></div>

            <p
                class="reference font-sans-clean fade-up-delay"
                :class="{ 'skeleton': loading }"
                x-text="loading ? '' : referenceLine"
            ></p>
        </div>
    </main>

    <script>
        function umbralApp() {
            return {
                theme: 'light',
                loading: true,
                verse: null,

                get referenceLine() {
                    if (!this.verse) return '';
                    const book = this.verse.book || '';
                    const chapter = this.verse.chapter;
                    const verse = this.verse.verse;
                    return `${book} ${chapter}:${verse} · BJ · Jerusalén`;
                },

                init() {
                    const storedTheme = localStorage.getItem('umbral-theme');
                    if (storedTheme === 'dark' || storedTheme === 'light') {
                        this.theme = storedTheme;
                    } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        this.theme = 'dark';
                    }

                    this.loadVerse();
                },

                toggleTheme() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('umbral-theme', this.theme);
                },

                today() {
                    const d = new Date();
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${y}-${m}-${day}`;
                },

                async loadVerse() {
                    const today = this.today();
                    const cachedDate = localStorage.getItem('umbral-verse-date');
                    const cachedData = localStorage.getItem('umbral-verse-data');

                    if (cachedDate === today && cachedData) {
                        try {
                            this.verse = JSON.parse(cachedData);
                            this.loading = false;
                            return;
                        } catch (e) {
                            // fall through to fetch
                        }
                    }

                    try {
                        const res = await fetch('/api/verse', {
                            headers: { 'Accept': 'application/json' },
                        });
                        if (!res.ok) throw new Error('verse fetch failed');
                        const data = await res.json();
                        this.verse = data;
                        localStorage.setItem('umbral-verse-date', data.date || today);
                        localStorage.setItem('umbral-verse-data', JSON.stringify(data));
                    } catch (e) {
                        this.verse = {
                            text: 'No se pudo cargar el versículo. Intenta de nuevo más tarde.',
                            reference: '',
                            book: 'UMBRAL',
                            chapter: 0,
                            verse: 0,
                            date: today,
                        };
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</body>
</html>
