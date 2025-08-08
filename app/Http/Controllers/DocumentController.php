<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'documentable_type' => 'required|string',
            'documentable_id' => 'required|integer',
            'file' => 'required|file',
            'name' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('documents', 'public');

        Document::create([
            'documentable_type' => $validated['documentable_type'],
            'documentable_id' => $validated['documentable_id'],
            'name' => $validated['name'] ?? $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
        ]);

        return redirect()->back()->with('success', 'Document uploaded.');
    }

    public function sign(Document $document)
    {
        $document->signature_request_id = (string) Str::uuid();
        $document->save();

        return redirect()->back()->with('success', 'Document sent for signature.');
    }

    public function download(Document $document)
    {
        return Storage::disk('public')->download($document->file_path, $document->name);
    }

    public function callback(Request $request)
    {
        $id = $request->input('signature_request_id');
        if ($id) {
            $document = Document::where('signature_request_id', $id)->first();
            if ($document) {
                $document->signed_at = now();
                $document->save();
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
