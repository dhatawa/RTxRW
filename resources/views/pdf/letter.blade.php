<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $number }}</title>
    <style>
        @page { margin: 2cm 2.2cm 2cm 2.5cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; color: #111; }
        .kop { border-bottom: 3px double #111; padding-bottom: 8px; text-align: center; }
        .kop .l1 { font-size: 14pt; font-weight: bold; letter-spacing: 1px; }
        .kop .l2 { font-size: 12pt; font-weight: bold; }
        .kop .l3 { font-size: 10pt; }
        .title { text-align: center; margin-top: 22px; }
        .title h1 { font-size: 13pt; text-decoration: underline; margin: 0; letter-spacing: .5px; }
        .title p { margin: 2px 0 0; font-size: 11pt; }
        .content { margin-top: 20px; text-align: justify; }
        .content p { margin: 0 0 10px; }
        table.identity { margin: 4px 0 12px 28px; border-collapse: collapse; }
        table.identity td { padding: 1px 4px; vertical-align: top; }
        table.identity td.l { width: 150px; }
        table.identity td.c { width: 10px; }
        .signs { width: 100%; margin-top: 28px; }
        .signs td { width: 50%; text-align: center; vertical-align: top; }
        .sign-name { margin-top: 62px; font-weight: bold; text-decoration: underline; }
        .meta { position: fixed; bottom: -0.6cm; left: 0; right: 0; border-top: 1px solid #999; padding-top: 6px; font-family: Helvetica, Arial, sans-serif; font-size: 7.5pt; color: #444; }
        .meta td { vertical-align: middle; }
    </style>
</head>
<body>
    <div class="kop">
        <div class="l1">RUKUN WARGA {{ $rw->number }}</div>
        <div class="l2">KELURAHAN {{ mb_strtoupper($rw->kelurahan) }} · KECAMATAN {{ mb_strtoupper($rw->kecamatan) }}</div>
        <div class="l3">{{ mb_strtoupper($rw->city) }}, {{ mb_strtoupper($rw->province) }}@if ($rw->postal_code) {{ $rw->postal_code }}@endif</div>
        @if ($rw->secretariat_address)<div class="l3">Sekretariat: {{ $rw->secretariat_address }}</div>@endif
    </div>

    <div class="title">
        <h1>{!! $sections['title'] !!}</h1>
        <p>Nomor: {{ $number }}</p>
    </div>

    <div class="content">
        {!! $sections['opening'] !!}
        {!! $sections['body'] !!}
        {!! $sections['closing'] !!}
    </div>

    @php
        $signatory = $type->signatory;
        $place = preg_replace('/^(Kota|Kabupaten)\s+/i', '', $rw->city);
    @endphp
    <table class="signs">
        <tr>
            @if ($signatory === \App\Enums\Signatory::Rt)
                <td></td>
                <td>
                    {{ $place }}, {{ $date->translatedFormat('j F Y') }}<br>
                    Ketua RT {{ $rt->number }} RW {{ $rw->number }}
                    <div class="sign-name">{{ $rt->ketua?->name ?? '..............................' }}</div>
                </td>
            @elseif ($signatory === \App\Enums\Signatory::Rw)
                <td></td>
                <td>
                    {{ $place }}, {{ $date->translatedFormat('j F Y') }}<br>
                    Ketua RW {{ $rw->number }}
                    <div class="sign-name">{{ $rw->ketua?->name ?? '..............................' }}</div>
                </td>
            @else
                <td>
                    Mengetahui,<br>
                    Ketua RW {{ $rw->number }}
                    <div class="sign-name">{{ $rw->ketua?->name ?? '..............................' }}</div>
                </td>
                <td>
                    {{ $place }}, {{ $date->translatedFormat('j F Y') }}<br>
                    Ketua RT {{ $rt->number }} RW {{ $rw->number }}
                    <div class="sign-name">{{ $rt->ketua?->name ?? '..............................' }}</div>
                </td>
            @endif
        </tr>
    </table>

    <div class="meta">
        <table style="width:100%">
            <tr>
                @if (! empty($qr))
                    <td style="width:62px"><img src="{{ $qr }}" style="width:56px;height:56px" alt=""></td>
                @endif
                <td>
                    Surat ini diterbitkan secara elektronik melalui RTxRW setelah disetujui Ketua RT dan Ketua RW.
                    Keaslian dapat diperiksa dengan memindai kode QR atau membuka {{ $verifyUrl }}<br>
                    Kode verifikasi: <strong>{{ $verificationCode }}</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
