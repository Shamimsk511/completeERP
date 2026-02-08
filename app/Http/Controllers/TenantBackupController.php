<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantBackupService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TenantBackupController extends Controller
{
    protected TenantBackupService $backupService;

    public function __construct(TenantBackupService $backupService)
    {
        $this->backupService = $backupService;
        $this->middleware('role:Super Admin');
    }

    public function create(Tenant $tenant)
    {
        $result = $this->backupService->create($tenant->id);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', "Backup created for {$tenant->name}: {$result['filename']} ({$result['size']})");
    }

    public function download(Tenant $tenant, string $filename)
    {
        $path = $this->backupService->getPath($tenant->id, $filename);

        if (!$path) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return response()->download($path);
    }

    public function delete(Tenant $tenant, string $filename)
    {
        if ($this->backupService->delete($tenant->id, $filename)) {
            return redirect()->back()->with('success', 'Backup deleted.');
        }

        return redirect()->back()->with('error', 'Failed to delete backup.');
    }

    public function restore(Request $request, Tenant $tenant)
    {
        $request->validate([
            'backup_filename' => ['nullable', 'string'],
            'backup_file' => ['nullable', 'file', 'mimes:sql', 'max:512000'],
        ]);

        if ($request->hasFile('backup_file')) {
            $result = $this->backupService->restoreFromUpload($tenant->id, $request->file('backup_file'));
        } elseif ($request->filled('backup_filename')) {
            $result = $this->backupService->restore($tenant->id, $request->input('backup_filename'));
        } else {
            return redirect()->back()->with('error', 'Please select or upload a backup file.');
        }

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }
}
