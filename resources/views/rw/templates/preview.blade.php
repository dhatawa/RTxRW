<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pratinjau · {{ $type->name }}</title>
    <style>
        body { background: #e5e8e4; margin: 0; padding: 24px 12px; font-family: "Times New Roman", Times, serif; color: #111; }
        .note { max-width: 21cm; margin: 0 auto 12px; font: 14px/1.5 system-ui, sans-serif; color: #545c58; }
        .page { background: #fff; max-width: 21cm; margin: 0 auto; padding: 2cm 2.2cm; box-shadow: 0 1px 2px rgba(0,0,0,.08); font-size: 12pt; line-height: 1.5; box-sizing: border-box; }
        .kop { border-bottom: 3px double #111; padding-bottom: 8px; text-align: center; }
        .kop .l1 { font-size: 14pt; font-weight: bold; } .kop .l2 { font-weight: bold; } .kop .l3 { font-size: 10pt; }
        h1 { font-size: 13pt; text-decoration: underline; text-align: center; margin: 22px 0 0; }
        .num { text-align: center; font-size: 11pt; margin: 2px 0 20px; }
        .content { text-align: justify; } .content p { margin: 0 0 10px; }
        .v { background: #fbf2df; }
        table.identity { margin: 4px 0 12px 28px; border-collapse: collapse; }
        table.identity td { padding: 1px 4px; vertical-align: top; } table.identity td.l { width: 150px; }
    </style>
</head>
<body>
    <p class="note">Pratinjau dengan data contoh. Bagian berlatar kuning diisi otomatis dari data pengajuan. Tanda tangan dan kode QR muncul pada surat asli.</p>
    <div class="page">
        <div class="kop">
            <div class="l1">RUKUN WARGA {{ $rw->number }}</div>
            <div class="l2">KELURAHAN {{ mb_strtoupper($rw->kelurahan) }} · KECAMATAN {{ mb_strtoupper($rw->kecamatan) }}</div>
            <div class="l3">{{ mb_strtoupper($rw->city) }}, {{ mb_strtoupper($rw->province) }}</div>
        </div>
        <h1>{!! $sections['title'] !!}</h1>
        <p class="num">Nomor: 001/{{ $type->code }}/RT{{ $rt?->number ?? '01' }}/RW{{ $rw->number }}/…</p>
        <div class="content">
            {!! $sections['opening'] !!}
            {!! $sections['body'] !!}
            {!! $sections['closing'] !!}
        </div>
    </div>
</body>
</html>
