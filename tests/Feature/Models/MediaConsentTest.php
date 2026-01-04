<?php

use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\MediaConsent;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

it('belongs to a consent document', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    // Create a valid media_id if media table exists, otherwise use a placeholder
    $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
        'model_type' => 'App\Models\Test',
        'model_id' => 1,
        'collection_name' => 'test',
        'name' => 'test',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]) : 999999;

    $mediaConsent = MediaConsent::factory()->create([
        'media_id' => $mediaId,
        'consent_document_id' => $document->id,
    ]);

    expect($mediaConsent->consentDocument)->toBeInstanceOf(Document::class)
        ->and($mediaConsent->consentDocument->id)->toBe($document->id);
});

it('can have null consent document', function () {
    // Create a valid media_id if media table exists, otherwise use a placeholder
    $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
        'model_type' => 'App\Models\Test',
        'model_id' => 1,
        'collection_name' => 'test',
        'name' => 'test',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]) : 999999;

    $mediaConsent = MediaConsent::factory()->create([
        'media_id' => $mediaId,
        'consent_document_id' => null,
    ]);

    expect($mediaConsent->consentDocument)->toBeNull();
});

it('sets consent_document_id to null when document is deleted', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    // Create a valid media_id if media table exists, otherwise use a placeholder
    $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
        'model_type' => 'App\Models\Test',
        'model_id' => 1,
        'collection_name' => 'test',
        'name' => 'test',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]) : 999999;

    $mediaConsent = MediaConsent::factory()->create([
        'media_id' => $mediaId,
        'consent_document_id' => $document->id,
    ]);

    // nullOnDelete() only works with permanent deletes (forceDelete), not soft deletes
    $document->forceDelete();
    $mediaConsent->refresh();

    expect($mediaConsent->consent_document_id)->toBeNull()
        ->and($mediaConsent->consentDocument)->toBeNull();
});
