@props([
    'title' => null,
    'metaDescription' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <link rel="preload" as="image" href="{{ asset('assets/img/logo.jpeg') }}" fetchpriority="high">
    </head>
    <body class="login-shell-body antialiased">
        <div class="login-shell-root">
            <div class="login-shell-aura" aria-hidden="true">
                <span class="login-shell-aura-glow login-shell-aura-glow--primary"></span>
                <span class="login-shell-aura-glow login-shell-aura-glow--secondary"></span>
            </div>

            <main class="login-shell-frame">
                <section class="login-shell-hero" aria-labelledby="login-shell-title">
                    <div class="login-shell-brand-row">
                        <a href="{{ route('login') }}" class="login-shell-brand-link" wire:navigate>
                            <span class="login-shell-brand-badge">Sekawan Coffee</span>
                            <span class="login-shell-brand-name">Ruang operasional kedai</span>
                        </a>
                    </div>

                    <div class="login-shell-copy">
                        <p class="login-shell-eyebrow">Hangat, rapi, siap dipakai setiap buka shift</p>
                        <h1 id="login-shell-title" class="login-shell-title">Masuk ke meja kerja Sekawan Coffee dengan ritme yang tenang dan premium.</h1>
                        <p class="login-shell-description">
                            Pantau pesanan, cek stok, dan mulai pelayanan dari satu ruang login yang terasa seperti meja bar
                            yang sudah siap sebelum pelanggan pertama datang.
                        </p>
                    </div>

                    <div class="login-shell-media">
                        <figure class="login-shell-media-card">
                            <img
                                src="{{ asset('assets/img/logo.jpeg') }}"
                                alt="Logo Sekawan Coffee pada latar hangat yang mewakili identitas kedai"
                                width="1376"
                                height="768"
                                class="login-shell-media-image"
                                fetchpriority="high"
                            >
                            <figcaption class="login-shell-media-caption">
                                Identitas visual utama yang menyambut admin dan kasir sejak layar pertama.
                            </figcaption>
                        </figure>

                        <div class="login-shell-highlights" aria-label="Keunggulan permukaan login">
                            <article class="login-shell-highlight-card">
                                <p class="login-shell-highlight-label">Mode kerja</p>
                                <p class="login-shell-highlight-title">Fokus untuk buka toko</p>
                                <p class="login-shell-highlight-copy">Permukaan split yang hangat menjaga form tetap terbaca sambil menegaskan brand Sekawan Coffee.</p>
                            </article>

                            <article class="login-shell-highlight-card">
                                <p class="login-shell-highlight-label">Untuk operasional harian</p>
                                <ul class="login-shell-highlight-list">
                                    <li>Kasir dan admin langsung melihat form login tanpa langkah tambahan.</li>
                                    <li>Logo brand tampil stabil dengan dimensi tetap agar tidak melonjak saat dimuat.</li>
                                    <li>Shell otomatis menumpuk rapi di layar mobile dan tetap lapang di desktop.</li>
                                </ul>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="login-shell-panel" aria-label="Form login Sekawan Coffee">
                    <div class="login-shell-panel-surface">
                        {{ $slot }}
                    </div>
                </section>
            </main>
        </div>

        @fluxScripts
    </body>
</html>
