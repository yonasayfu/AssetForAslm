<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? ($headerInfo['document_title'] ?? 'Document') }}</title>
        <style>
            * {
                box-sizing: border-box;
            }

            body {
                font-family: DejaVu Sans, Arial, sans-serif;
                color: #0f172a;
                margin: 24px;
                font-size: 12px;
            }

            .header {
                text-align: center;
                margin-bottom: 16px;
            }

            .header .logo {
                max-width: 140px;
                max-height: 60px;
                margin: 0 auto 8px auto;
            }

            .header .organization {
                font-weight: 700;
                font-size: 18px;
                margin-bottom: 4px;
            }

            .header .document-title {
                font-size: 12px;
                color: #475569;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 24px;
            }

            th,
            td {
                border: 1px solid #e2e8f0;
                padding: 6px 8px;
                text-align: left;
            }

            thead {
                background: #f8fafc;
            }

            .kv-row {
                margin-bottom: 6px;
                display: flex;
                align-items: baseline;
            }

            .kv-label {
                width: 160px;
                font-weight: 600;
                color: #1e293b;
            }

            .kv-value {
                flex: 1;
            }

            .footer {
                position: fixed;
                bottom: 12px;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 10px;
                color: #64748b;
            }

            .page-number::after {
                content: counter(page) " / " counter(pages);
            }
        </style>
    </head>
    <body>
        <div class="header">
            @if(!empty($headerInfo['logo']) && file_exists($headerInfo['logo']))
                <img class="logo" src="{{ $headerInfo['logo'] }}" alt="Logo">
            @endif
            <div class="organization">
                {{ $headerInfo['organization_name'] ?? config('app.name', 'Asset Management') }}
            </div>
            <div class="document-title">
                {{ $headerInfo['document_title'] ?? ($title ?? 'Document') }}
            </div>
        </div>

        @php
            $isCollection = is_iterable($data) && !($data instanceof \Illuminate\Contracts\Support\Arrayable && method_exists($data, 'toArray') && isset($data['id']));
            $columns = $columns ?? [];
        @endphp

        @if($isCollection)
            @php
                $items = $data instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $data->items() : $data;
                $indexBase = ($data instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $data->firstItem()) ? ($data->firstItem() - 1) : 0;
            @endphp
            <table>
                <thead>
                    <tr>
                        @foreach($columns as $column)
                            <th>{{ $column['label'] ?? ucfirst($column['key'] ?? '') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $row)
                        <tr>
                            @foreach($columns as $column)
                                @php
                                    $key = $column['key'] ?? '';
                                    $value = '';

                                    if (isset($column['transform']) && is_callable($column['transform'])) {
                                        $value = call_user_func($column['transform'], $row);
                                    } elseif ($key === 'index') {
                                        $value = $indexBase + $loop->parent->iteration;
                                    } else {
                                        $value = data_get($row, $key, '');
                                    }

                                    if (is_bool($value)) {
                                        $value = $value ? 'Yes' : 'No';
                                    }

                                    if ($value instanceof \Illuminate\Support\Collection) {
                                        $value = $value->implode(', ');
                                    } elseif (is_array($value)) {
                                        $value = implode(', ', $value);
                                    }

                                    $value = $value === '' ? '—' : $value;
                                @endphp
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}" style="text-align: center; color: #94a3b8; padding: 18px;">
                                No records found for this export.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @else
            @php $record = $data; @endphp
            <div>
                @foreach($columns as $column)
                    @php
                        $key = $column['key'] ?? '';
                        $value = '';

                        if (isset($column['transform']) && is_callable($column['transform'])) {
                            $value = call_user_func($column['transform'], $record);
                        } else {
                            $value = data_get($record, $key, '');
                        }

                        if (is_bool($value)) {
                            $value = $value ? 'Yes' : 'No';
                        }

                        if ($value instanceof \Illuminate\Support\Collection) {
                            $value = $value->implode(', ');
                        } elseif (is_array($value)) {
                            $value = implode(', ', $value);
                        }

                        $value = $value === '' ? '—' : $value;
                    @endphp
                    <div class="kv-row">
                        <div class="kv-label">{{ $column['label'] ?? ucfirst($key) }}</div>
                        <div class="kv-value">{{ $value }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        @unless(!empty($hide_footer))
            <div class="footer">
                <div>Generated on {{ $footerInfo['generated_date'] ?? now()->format('F j, Y, g:i a') }}</div>
                <div>{{ $footerInfo['app_name'] ?? config('app.name') }}</div>
                <div>Page <span class="page-number"></span></div>
            </div>
        @endunless
    </body>
</html>

