@props(['title' => null, 'description' => null])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    @include('partials.head')
</head>
<body class="min-h-full bg-canvas">
    <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(0,28rem)] xl:grid-cols-[minmax(0,1fr)_minmax(0,32rem)]">
        <div class="flex flex-col px-4 py-6 sm:px-8">
            <a href="{{ route('home') }}" class="self-start no-underline" aria-label="RTxRW, beranda"><x-wordmark /></a>
            <main id="konten" class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-10">
                {{ $slot }}
            </main>
        </div>

        <aside class="hidden flex-col justify-between bg-primary p-10 text-white lg:flex">
            <x-wordmark inverse size="lg" />
            <div>
                <p class="text-2xl font-semibold leading-snug">Surat pengantar RT/RW, diurus dari rumah.</p>
                <ol class="mt-8 space-y-5 text-[0.9375rem] text-white/80">
                    @foreach (['Isi formulir dan unggah dokumen dari ponsel.', 'Ketua RT memeriksa, lalu Ketua RW menyetujui.', 'Surat ber-QR terbit dan bisa langsung diunduh.'] as $i => $step)
                        <li class="flex gap-4">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full border border-white/40 text-sm font-semibold text-white">{{ $i + 1 }}</span>
                            <span class="pt-0.5">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
            <p class="text-sm text-white/60">Butuh bantuan? Hubungi Ketua RT tempat Anda berdomisili.</p>
        </aside>
    </div>
</body>
</html>
