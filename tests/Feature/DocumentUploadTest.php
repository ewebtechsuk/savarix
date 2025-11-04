<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Property;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    public function test_upload_persists_documentable_columns(): void
    {
        $this->withoutMiddleware();

        Storage::fake('public');

        $property = Property::factory()->create();

        $response = $this->post(route('documents.upload'), [
            'documentable_type' => Property::class,
            'documentable_id' => $property->id,
            'file' => UploadedFile::fake()->create('contract.pdf', 10, 'application/pdf'),
            'name' => 'Lease Agreement',
        ]);

        $response->assertRedirect();

        $document = Document::first();

        $this->assertNotNull($document);
        $this->assertSame(Property::class, $document->documentable_type);
        $this->assertSame($property->id, $document->documentable_id);
        $this->assertSame('Lease Agreement', $document->name);

        Storage::disk('public')->assertExists($document->file_path);
    }
}
