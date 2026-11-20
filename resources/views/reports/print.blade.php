<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Official Document Register Report — Lord Mayor's Office</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&family=Libre+Franklin:wght@600;700&family=Source+Sans+3:wght@400;600&display=swap">
    <style>
        body {
            font-family: 'Source Sans 3', Arial, sans-serif;
            color: #23261F;
            font-size: 12.5px;
            margin: 28px;
            background: #fff;
        }

        h1 {
            font-family: 'Libre Franklin', Arial, sans-serif;
            font-size: 20px;
            margin: 4px 0 0;
            color: #23261F;
        }

        .sub {
            font-family: 'Libre Franklin', Arial, sans-serif;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #8E1C2A;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 3px solid #6A1421;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .meta {
            text-align: right;
            font-size: 12px;
            color: #5D6659;
            line-height: 1.5;
        }

        .meta strong {
            color: #23261F;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #CBD3C3;
            padding: 7px 9px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #EDF0E8;
            font-family: 'Libre Franklin', Arial, sans-serif;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #3E5A43;
        }

        .mono {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 11.5px;
        }

        .no-print {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .btn {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid #CBD3C3;
            background: #FAFBF7;
            color: #23261F;
        }

        .btn.pri {
            background: #8E1C2A;
            border-color: #8E1C2A;
            color: #fff;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn pri">Print / Save as PDF</button>
        <a href="{{ route('reports.index') }}" class="btn">Back to reports</a>
    </div>

    <div class="report-header">
        <div>
            <div class="sub">Office of the Lord Mayor</div>
            <h1>Official Document Register Report</h1>
        </div>
        <div class="meta">
            <div>Generated: <strong>{{ now()->format('j M Y, H:i') }}</strong></div>
            <div>Prepared by: <strong>{{ $user->name }} ({{ $user->role?->name }})</strong></div>
            <div>Total records: <strong>{{ $documents->total() }}</strong></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Internal ID</th>
                <th>Reference No.</th>
                <th>Subject</th>
                <th>Organisation</th>
                <th>Category</th>
                <th>Date Received</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $doc)
                <tr>
                    <td class="mono">{{ $doc->internal_reference }}</td>
                    <td class="mono">{{ $doc->official_reference ?? '—' }}</td>
                    <td>{{ $doc->title }}</td>
                    <td>{{ $doc->organization }}</td>
                    <td>{{ $doc->category?->name }}</td>
                    <td class="mono">{{ $doc->date_received?->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
