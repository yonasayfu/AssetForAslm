<?php

namespace App\Support\Exports;

use App\Models\DataExport;
use App\Support\Storage\StoragePath;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

trait HandlesDataExport
{
    /**
     * Generate an export (CSV or PDF) for the given model.
     */
    protected function handleExport(Request $request, string $modelClass, array $config, array $options = []): BinaryFileResponse|BaseResponse
    {
        /** @var Builder $query */
        $query = $modelClass::query();

        if (method_exists($this, 'applyFilters')) {
            $this->applyFilters($query, $request);
        }

        if (method_exists($this, 'applySearch')) {
            $this->applySearch($query, $request->input('search'));
        }

        if (method_exists($this, 'applySorting')) {
            $this->applySorting($query, $request);
        }

        $type = $request->input('type', 'csv');

        return match ($type) {
            'csv' => $this->exportCsv($request, $query, $config, $options),
            'pdf' => $this->exportPdf($request, $query, $config, $options),
            default => abort(400, 'Unsupported export type.'),
        };
    }

    /**
     * Export current filtered dataset to CSV, persist the file, and return it.
     */
    protected function exportCsv(Request $request, Builder $query, array $config, array $options = []): BinaryFileResponse
    {
        $csvConfig = $config['csv'] ?? null;

        if (! $csvConfig || empty($csvConfig['headers']) || empty($csvConfig['fields'])) {
            abort(500, "CSV export configuration must include 'headers' and 'fields'.");
        }

        if (isset($csvConfig['with_relations']) && is_array($csvConfig['with_relations'])) {
            $query->with($csvConfig['with_relations']);
        }

        if (isset($csvConfig['query_callback']) && is_callable($csvConfig['query_callback'])) {
            call_user_func($csvConfig['query_callback'], $query, $request);
        }

        $data = $query->get();

        $label = $options['label'] ?? ($config['label'] ?? class_basename($query->getModel()));
        $type = $options['type'] ?? ($config['type'] ?? Str::snake(class_basename($query->getModel())));
        $filenamePrefix = $csvConfig['filename_prefix'] ?? ($config['filename_prefix'] ?? Str::slug($label));
        $filename = $filenamePrefix.'_'.now()->format('Ymd_His').'.csv';

        $disk = $options['disk'] ?? 'local';
        $directory = trim($options['directory'] ?? StoragePath::exports(), '/');
        Storage::disk($disk)->makeDirectory($directory);

        $path = $directory.'/'.$filename;
        $absolutePath = Storage::disk($disk)->path($path);

        $handle = fopen($absolutePath, 'w');

        if (! $handle) {
            abort(500, 'Unable to open export file for writing.');
        }

        // Prepend UTF-8 BOM for Excel compatibility.
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $csvConfig['headers']);

        foreach ($data as $index => $row) {
            $line = [];

            foreach ($csvConfig['fields'] as $field) {
                if ($field === 'index') {
                    $line[] = $index + 1;

                    continue;
                }

                if (is_array($field)) {
                    $source = $field['field'] ?? null;
                    $value = $source ? data_get($row, $source) : null;

                    if (isset($field['transform']) && is_callable($field['transform'])) {
                        $value = call_user_func($field['transform'], $value, $row);
                    }

                    if (($value === null || $value === '') && array_key_exists('default', $field)) {
                        $value = $field['default'];
                    }

                    $line[] = $value ?? '';

                    continue;
                }

                $line[] = data_get($row, $field, '');
            }

            fputcsv($handle, $line);
        }

        fclose($handle);

        $this->recordExport($request, [
            'name' => $label,
            'type' => $type,
            'format' => 'csv',
            'file_path' => $path,
            'record_count' => $data->count(),
            'filters' => $request->query(),
        ]);

        return response()->download($absolutePath, $filename)->deleteFileAfterSend(false);
    }

    /**
     * Generate a PDF export (all records).
     */
    protected function exportPdf(Request $request, Builder $query, array $config, array $options = []): BaseResponse
    {
        $pdfConfig = $config['pdf'] ?? null;

        if (! $pdfConfig || empty($pdfConfig['view'])) {
            abort(500, "PDF export configuration is missing the 'view' key.");
        }

        if (isset($pdfConfig['with_relations']) && is_array($pdfConfig['with_relations'])) {
            $query->with($pdfConfig['with_relations']);
        }

        $data = $query->get();

        $paper = $pdfConfig['paper'] ?? $pdfConfig['paper_size'] ?? 'a4';
        $orientation = $pdfConfig['orientation'] ?? 'portrait';

        $headerInfo = $this->buildHeaderInfo($pdfConfig, $pdfConfig['document_title'] ?? $config['label'] ?? 'Export');
        $footerInfo = $this->buildFooterInfo($pdfConfig);

        $pdf = Pdf::loadView($pdfConfig['view'], [
            'data' => $data,
            'title' => $pdfConfig['document_title'] ?? $config['label'] ?? 'Export',
            'columns' => $pdfConfig['columns'] ?? [],
            'hide_footer' => $pdfConfig['hide_footer'] ?? false,
            'headerInfo' => $headerInfo,
            'footerInfo' => $footerInfo,
        ])->setPaper($paper, $orientation);

        $filenamePrefix = $pdfConfig['filename_prefix'] ?? ($config['filename_prefix'] ?? Str::slug($config['label'] ?? 'export'));
        $filename = $filenamePrefix.'_'.now()->format('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }

    protected function handlePrintAll(Request $request, string $modelClass, array $config): BaseResponse
    {
        return $this->exportPdf($request, $modelClass::query(), $config);
    }

    protected function handlePrintCurrent(Request $request, string $modelClass, array $config): BaseResponse
    {
        $query = $modelClass::query();

        if (method_exists($this, 'applyFilters')) {
            $this->applyFilters($query, $request);
        }

        if (method_exists($this, 'applySearch')) {
            $this->applySearch($query, $request->input('search'));
        }

        if (method_exists($this, 'applySorting')) {
            $this->applySorting($query, $request);
        }

        $currentConfig = $config['current_page'] ?? $config['pdf'] ?? [];

        if (isset($currentConfig['with_relations']) && is_array($currentConfig['with_relations'])) {
            $query->with($currentConfig['with_relations']);
        }

        $perPage = (int) $request->input('per_page', 10);
        $data = $query->paginate($perPage);

        $paper = $currentConfig['paper'] ?? $currentConfig['paper_size'] ?? 'a4';
        $orientation = $currentConfig['orientation'] ?? 'portrait';

        $headerInfo = $this->buildHeaderInfo($currentConfig, $currentConfig['document_title'] ?? $config['label'] ?? 'Current View');
        $footerInfo = $this->buildFooterInfo($currentConfig);

        $pdf = Pdf::loadView($currentConfig['view'] ?? 'pdf-layout', [
            'data' => $data,
            'title' => $currentConfig['document_title'] ?? $config['label'] ?? 'Current View',
            'columns' => $currentConfig['columns'] ?? [],
            'hide_footer' => $currentConfig['hide_footer'] ?? false,
            'headerInfo' => $headerInfo,
            'footerInfo' => $footerInfo,
        ])->setPaper($paper, $orientation);

        $filenamePrefix = $currentConfig['filename_prefix'] ?? ($config['filename_prefix'] ?? Str::slug($config['label'] ?? 'export'));

        return $pdf->download($filenamePrefix.'_'.now()->format('Ymd_His').'.pdf');
    }

    protected function handlePrintSingle(Request $request, Model $model, array $config): BaseResponse
    {
        $singleConfig = $config['single_record'] ?? $config['pdf'] ?? [];

        if (isset($singleConfig['with_relations']) && is_array($singleConfig['with_relations'])) {
            $model->load($singleConfig['with_relations']);
        }

        $paper = $singleConfig['paper'] ?? $singleConfig['paper_size'] ?? 'a4';
        $orientation = $singleConfig['orientation'] ?? 'portrait';

        $headerInfo = $this->buildHeaderInfo($singleConfig, $singleConfig['document_title'] ?? $config['label'] ?? 'Record Details');
        $footerInfo = $this->buildFooterInfo($singleConfig);

        $pdf = Pdf::loadView($singleConfig['view'] ?? 'pdf-layout', [
            'data' => $model,
            'title' => $singleConfig['document_title'] ?? $config['label'] ?? 'Record Details',
            'columns' => $singleConfig['columns'] ?? [],
            'hide_footer' => $singleConfig['hide_footer'] ?? false,
            'headerInfo' => $headerInfo,
            'footerInfo' => $footerInfo,
        ])->setPaper($paper, $orientation);

        $filenamePrefix = $singleConfig['filename_prefix'] ?? ($config['filename_prefix'] ?? Str::slug($config['label'] ?? 'export'));

        return $pdf->download($filenamePrefix.'_'.$model->getKey().'_'.now()->format('Ymd_His').'.pdf');
    }

    /**
     * Persist export metadata for the download center.
     */
    protected function recordExport(Request $request, array $payload): ?DataExport
    {
        if (! class_exists(DataExport::class)) {
            return null;
        }

        return DataExport::create([
            'user_id' => optional($request->user())->id,
            'name' => $payload['name'],
            'type' => $payload['type'],
            'format' => $payload['format'],
            'status' => $payload['status'] ?? DataExport::STATUS_COMPLETED,
            'file_path' => $payload['file_path'],
            'record_count' => $payload['record_count'] ?? 0,
            'filters' => Arr::wrap($payload['filters'] ?? []),
            'completed_at' => $payload['completed_at'] ?? now(),
        ]);
    }

    protected function buildHeaderInfo(array $config, string $documentTitle): array
    {
        $brand = ExportConfig::brandHeader($documentTitle);
        $header = $config['header_info'] ?? [];

        return array_merge($brand, $header, [
            'document_title' => $documentTitle,
        ]);
    }

    protected function buildFooterInfo(array $config): array
    {
        $footer = [
            'generated_date' => now()->format('F j, Y, g:i a'),
            'app_name' => config('app.name'),
        ];

        if (! empty($config['footer_info']) && is_array($config['footer_info'])) {
            $footer = array_merge($footer, $config['footer_info']);
        }

        return $footer;
    }
}
