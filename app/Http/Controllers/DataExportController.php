<?php

namespace App\Http\Controllers;

use App\Models\DataExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DataExportController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedPerPage = [5, 10, 25, 50, 100];
        $perPage = $request->integer('per_page', 5);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 5;
        }
        $search = trim((string) $request->query('search', ''));

        $exports = DataExport::query()
            ->where('user_id', $request->user()->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (DataExport $export) {
                return [
                    'uuid' => $export->uuid,
                    'name' => $export->name,
                    'type' => $export->type,
                    'format' => strtoupper($export->format),
                    'status' => $export->status,
                    'record_count' => $export->record_count,
                    'completed_at' => optional($export->completed_at)->toDateTimeString(),
                    'completed_at_human' => optional($export->completed_at)->diffForHumans(),
                    'download_url' => route('exports.download', $export),
                ];
            });

        return Inertia::render('Exports/Index', [
            'exports' => $exports,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function download(Request $request, DataExport $export)
    {
        $this->abortIfUnauthorized($request, $export);

        if (! Storage::disk('local')->exists($export->file_path)) {
            return redirect()
                ->route('exports.index')
                ->with('bannerStyle', 'danger')
                ->with('banner', 'Export file is no longer available. Please regenerate.');
        }

        $filename = basename($export->file_path);

        return Storage::disk('local')->download($export->file_path, $filename);
    }

    public function destroy(Request $request, DataExport $export): RedirectResponse
    {
        $this->abortIfUnauthorized($request, $export);

        Storage::disk('local')->delete($export->file_path);
        $export->delete();

        return redirect()
            ->route('exports.index')
            ->with('bannerStyle', 'info')
            ->with('banner', 'Export entry removed.');
    }

    protected function abortIfUnauthorized(Request $request, DataExport $export): void
    {
        abort_unless($export->user_id === optional($request->user())->id, 403);
    }
}
