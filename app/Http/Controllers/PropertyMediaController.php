<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyMediaController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $request->validate([
            'media.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);
        if ($request->hasFile('media')) {
            $order = $property->media()->max('order') ?? 0;
            foreach ($request->file('media') as $file) {
                $order++;
                $path = Storage::disk('public')->putFile('property_media', $file);
                $property->media()->create([
                    'file_path' => $path,
                    'type' => $file->getClientMimeType(),
                    'order' => $order,
                ]);
            }
        }
        return back()->with('success', 'Images uploaded.');
    }

    public function destroy(Property $property, PropertyMedia $media)
    {
        if ($media->property_id !== $property->id) {
            abort(403);
        }
        Storage::disk('public')->delete($media->file_path);
        $media->delete();
        return back()->with('success', 'Image deleted.');
    }
}
