<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentSignatureFlowTest extends TestCase
{
    public function test_document_signature_flow_updates_status_and_allows_download(): void
    {
        $this->withoutMiddleware();
        $this->useTenantDomain();

        Config::set('services.hellosign.endpoint', 'https://api.hellosign.test');
        Config::set('services.hellosign.key', 'test-token');

        Storage::fake('public');
        Storage::disk('public')->put('documents/lease-agreement.pdf', 'ORIGINAL-PDF');

        $property = Property::factory()->create();
        $document = Document::create([
            'documentable_type' => Property::class,
            'documentable_id' => $property->id,
            'name' => 'Lease Agreement.pdf',
            'file_path' => 'documents/lease-agreement.pdf',
        ]);

        $this->assertSame('documents/lease-agreement.pdf', $document->file_path);
        $this->assertSame('documents/lease-agreement.pdf', Document::find($document->id)->file_path);
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'file_path' => 'documents/lease-agreement.pdf',
        ]);

        Http::fake([
            'https://api.hellosign.test/signature_requests' => Http::response([
                'signature_request' => [
                    'id' => 'req-123',
                    'signing_url' => 'https://sign.hellosign.test/request/req-123',
                ],
            ], 201),
            'https://api.hellosign.test/signature_requests/req-123/file' => Http::response('SIGNED-PDF', 200, [
                'Content-Type' => 'application/pdf',
            ]),
        ]);

        $response = $this->post(route('documents.sign', ['document' => $document->id]), [
            'file_path' => 'documents/lease-agreement.pdf',
        ]);
        $response->assertRedirect();

        $document = $document->fresh();
        $this->assertSame('req-123', $document->signature_request_id);
        $this->assertNull($document->signed_at);

        Http::assertSent(function (Request $request) use ($document) {
            if ($request->method() !== 'POST') {
                return true;
            }

            $payload = $request->data();

            return $request->url() === 'https://api.hellosign.test/signature_requests'
                && $payload['title'] === $document->name
                && base64_decode($payload['file']) === 'ORIGINAL-PDF'
                && $payload['metadata']['document_id'] === (string) $document->id
                && $payload['callback_url'] === route('signing.callback');
        });

        $completedAt = Carbon::parse('2024-01-05T10:15:30+00:00');

        $webhookResponse = $this->postJson(route('signing.callback'), [
            'signature_request_id' => 'req-123',
            'status' => 'completed',
            'completed_at' => $completedAt->toIso8601String(),
        ]);

        $webhookResponse->assertOk()->assertJson(['status' => 'ok']);

        $document = $document->fresh();
        $this->assertTrue($document->signed_at->equalTo($completedAt));

        $downloadResponse = $this->get(route('documents.downloadSigned', ['document' => $document->id]));
        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('Content-Type', 'application/pdf');
        $downloadResponse->assertHeader('Content-Disposition', 'attachment; filename="Lease Agreement-signed.pdf"');
        $this->assertSame('SIGNED-PDF', $downloadResponse->getContent());

        Http::assertSent(function (Request $request) {
            if ($request->method() !== 'GET') {
                return true;
            }

            return $request->url() === 'https://api.hellosign.test/signature_requests/req-123/file';
        });
    }
}
