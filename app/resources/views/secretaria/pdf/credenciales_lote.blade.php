<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Planilla de Credenciales - {{ $course->full_name }}</title>
    <style>
        @page {
            margin: 6mm;
            size: letter portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background: #ffffff;
            color: #0f172a;
        }
        .sheet {
            width: 100%;
        }
        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4mm 4mm;
        }
        .grid-td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .card-box {
            width: 85.6mm;
            height: 54mm;
            border: 1px solid #94a3b8;
            background: #ffffff;
            position: relative;
            overflow: hidden;
            border-radius: 4px;
        }
        .card-header {
            background: #0f172a;
            color: #ffffff;
            padding: 4px 6px 3px 6px;
            width: 100%;
        }
        .header-tbl {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-cell {
            width: 24px;
            vertical-align: middle;
        }
        .logo-cell img {
            width: 22px;
            height: 22px;
            border-radius: 3px;
        }
        .title-cell {
            vertical-align: middle;
            padding-left: 5px;
        }
        .school-name {
            font-size: 6.8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.1;
        }
        .school-sub {
            font-size: 4.8pt;
            color: #94a3b8;
        }
        .green-line {
            height: 2px;
            background: #16a34a;
            width: 100%;
        }
        .card-body-tbl {
            width: 100%;
            height: 38mm;
            border-collapse: collapse;
        }
        .photo-cell {
            width: 28mm;
            vertical-align: middle;
            text-align: center;
            padding-left: 4px;
        }
        .photo-frame {
            width: 25mm;
            height: 31mm;
            border: 1.5pt solid #0f172a;
            border-radius: 3px;
            background: #f1f5f9;
            overflow: hidden;
            display: block;
            margin: 0 auto;
        }
        .photo-frame img {
            width: 100%;
            height: 100%;
        }
        .photo-frame .placeholder {
            line-height: 31mm;
            font-size: 15pt;
            font-weight: bold;
            color: #475569;
        }
        .badge {
            font-size: 4.5pt;
            font-weight: bold;
            background: #16a34a;
            color: #ffffff;
            padding: 1.5px 3.5px;
            border-radius: 2px;
            margin-top: 2px;
            display: inline-block;
            text-transform: uppercase;
        }
        .info-cell {
            vertical-align: middle;
            padding-left: 6px;
            padding-right: 6px;
        }
        .student-name {
            font-size: 8pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.15;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .grade-badge-line {
            margin-bottom: 3px;
        }
        .grade-highlight {
            font-size: 6.8pt;
            font-weight: bold;
            color: #16a34a;
        }
        .data-tbl {
            font-size: 5.5pt;
            width: 100%;
            border-collapse: collapse;
        }
        .data-tbl td {
            padding: 0.8px 0;
        }
        .lbl {
            color: #64748b;
            font-weight: bold;
            width: 32px;
            font-size: 5pt;
            text-transform: uppercase;
        }
        .val {
            font-weight: bold;
            color: #0f172a;
        }
        .card-foot {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8fafc;
            border-top: 1px solid #cbd5e1;
            padding: 2.5px 6px;
        }
        .foot-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 4.8pt;
        }
        .foot-left {
            color: #334155;
            font-weight: bold;
        }
        .foot-right {
            text-align: right;
            font-weight: bold;
            color: #16a34a;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @php
        $chunks = $students->chunk(8);
    @endphp

    @foreach($chunks as $pageIndex => $pageStudents)
        <div class="sheet">
            <table class="grid-table">
                @foreach($pageStudents->chunk(2) as $row)
                    <tr>
                        @foreach($row as $st)
                            <td class="grid-td">
                                <div class="card-box">
                                    <div class="card-header">
                                        <table class="header-tbl">
                                            <tr>
                                                <td class="logo-cell">
                                                    @if($setting->logo_base64)
                                                        <img src="{{ $setting->logo_base64 }}">
                                                    @else
                                                        <span style="color:#16a34a;font-weight:bold;font-size:8pt">T</span>
                                                    @endif
                                                </td>
                                                <td class="title-cell">
                                                    <div class="school-name">{{ \Illuminate\Support\Str::limit($setting->school_name, 32) }}</div>
                                                    <div class="school-sub">CCT: {{ $setting->cct ?? 'OFICIAL' }} · CICLO {{ $setting->academic_year }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="green-line"></div>

                                    <table class="card-body-tbl">
                                        <tr>
                                            <td class="photo-cell">
                                                <div class="photo-frame">
                                                    @if($st->photo_base64)
                                                        <img src="{{ $st->photo_base64 }}">
                                                    @else
                                                        <div class="placeholder">{{ $st->initials }}</div>
                                                    @endif
                                                </div>
                                                <div class="badge">ESTUDIANTE</div>
                                            </td>
                                            <td class="info-cell">
                                                <div class="student-name">{{ $st->full_name }}</div>
                                                <div class="grade-badge-line">
                                                    <span class="grade-highlight">{{ optional($st->course)->name }} "{{ optional($st->course)->section }}"</span>
                                                </div>
                                                <table class="data-tbl">
                                                    <tr>
                                                        <td class="lbl">MAT:</td>
                                                        <td class="val">{{ $st->code }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="lbl">CURP:</td>
                                                        <td class="val">{{ $st->curp ?? $st->dni ?? 'N/D' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="lbl">TURNO:</td>
                                                        <td class="val">{{ optional($st->course)->shift ?? 'Matutino' }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <div class="card-foot">
                                        <table class="foot-tbl">
                                            <tr>
                                                <td class="foot-left">CICLO: {{ $setting->academic_year }}</td>
                                                <td class="foot-right">{{ $course->full_name }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        @endforeach
                        @if($row->count() < 2)
                            <td class="grid-td"></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </div>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
