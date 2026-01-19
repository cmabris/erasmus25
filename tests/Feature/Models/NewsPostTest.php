<?php

use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;

it('belongs to a program', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    expect($newsPost->program)->toBeInstanceOf(Program::class)
        ->and($newsPost->program->id)->toBe($program->id);
});

it('can have null program', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => null,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    expect($newsPost->program)->toBeNull();
});

it('belongs to an academic year', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    expect($newsPost->academicYear)->toBeInstanceOf(AcademicYear::class)
        ->and($newsPost->academicYear->id)->toBe($academicYear->id);
});

it('belongs to an author user', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    expect($newsPost->author)->toBeInstanceOf(User::class)
        ->and($newsPost->author->id)->toBe($user->id);
});

it('can have null author', function () {
    $academicYear = AcademicYear::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => null,
        'academic_year_id' => $academicYear->id,
        'author_id' => null,
    ]);

    expect($newsPost->author)->toBeNull();
});

it('belongs to a reviewer user', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();
    $reviewer = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'reviewed_by' => $reviewer->id,
    ]);

    expect($newsPost->reviewer)->toBeInstanceOf(User::class)
        ->and($newsPost->reviewer->id)->toBe($reviewer->id);
});

it('can have null reviewer', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => null,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
        'reviewed_by' => null,
    ]);

    expect($newsPost->reviewer)->toBeNull();
});

it('belongs to many tags', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);
    $tag1 = NewsTag::factory()->create();
    $tag2 = NewsTag::factory()->create();
    $tag3 = NewsTag::factory()->create();

    $newsPost->tags()->attach([$tag1->id, $tag2->id, $tag3->id]);

    expect($newsPost->tags)->toHaveCount(3)
        ->and($newsPost->tags->first())->toBeInstanceOf(NewsTag::class);
});

it('can attach and detach tags', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);
    $tag1 = NewsTag::factory()->create();
    $tag2 = NewsTag::factory()->create();

    $newsPost->tags()->attach($tag1->id);
    expect($newsPost->fresh()->tags)->toHaveCount(1);

    $newsPost->tags()->attach($tag2->id);
    expect($newsPost->fresh()->tags)->toHaveCount(2);

    $newsPost->tags()->detach($tag1->id);
    expect($newsPost->fresh()->tags)->toHaveCount(1)
        ->and($newsPost->fresh()->tags->first()->id)->toBe($tag2->id);
});

it('does not set program_id to null when program is soft deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $program->delete(); // Soft delete
    $newsPost->refresh();

    // With SoftDeletes, program_id is not set to null because the program still exists
    // However, Eloquent excludes soft-deleted records from relationships, so $newsPost->program will be null
    expect($newsPost->program_id)->toBe($program->id)
        ->and($newsPost->program)->toBeNull() // Eloquent excludes soft-deleted records
        ->and($program->fresh()->trashed())->toBeTrue();
});

it('is deleted in cascade when academic year is deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $academicYear->delete();

    expect(NewsPost::find($newsPost->id))->toBeNull();
});

it('sets author_id to null when author user is force deleted', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    // Force delete to trigger foreign key constraint
    $user->forceDelete();
    $newsPost->refresh();

    expect($newsPost->author_id)->toBeNull()
        ->and($newsPost->author)->toBeNull();
});

it('sets reviewed_by to null when reviewer user is force deleted', function () {
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();
    $reviewer = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'reviewed_by' => $reviewer->id,
    ]);

    // Force delete to trigger foreign key constraint
    $reviewer->forceDelete();
    $newsPost->refresh();

    expect($newsPost->reviewed_by)->toBeNull()
        ->and($newsPost->reviewer)->toBeNull();
});

it('removes tag relationships when news post is deleted', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);
    $tag = NewsTag::factory()->create();
    $newsPost->tags()->attach($tag->id);

    $newsPost->delete();

    expect($tag->newsPosts)->toHaveCount(0);
});

it('generates slug automatically when slug is empty', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::create([
        'academic_year_id' => $academicYear->id,
        'title' => 'Test News Post Title',
        'slug' => '', // Empty slug
        'content' => 'Test content',
        'status' => 'borrador',
        'author_id' => $user->id,
    ]);

    expect($newsPost->slug)->toBe('test-news-post-title');
});

// ============================================
// MEDIA LIBRARY TESTS
// ============================================

it('can add featured image to a news post', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('featured.jpg', 800, 600);

    $media = $newsPost->addMedia($image->getRealPath())
        ->usingName('Featured Image')
        ->usingFileName($image->getClientOriginalName())
        ->toMediaCollection('featured');

    expect($newsPost->hasMedia('featured'))->toBeTrue()
        ->and($newsPost->getFirstMedia('featured'))->not->toBeNull()
        ->and($media->collection_name)->toBe('featured');
});

it('can get media with callable filter', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('gallery1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('gallery2.jpg', 800, 600);

    $media1 = $newsPost->addMedia($image1->getRealPath())
        ->usingName('Gallery Image 1')
        ->usingFileName('gallery1.jpg')
        ->toMediaCollection('gallery');

    $newsPost->addMedia($image2->getRealPath())
        ->usingName('Gallery Image 2')
        ->usingFileName('gallery2.jpg')
        ->toMediaCollection('gallery');

    // Filter by name using callable
    $filtered = $newsPost->getMedia('gallery', fn ($m) => $m->name === 'Gallery Image 1');

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->id)->toBe($media1->id);
});

it('can get media with array filter', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('gallery1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('gallery2.jpg', 800, 600);

    $media1 = $newsPost->addMedia($image1->getRealPath())
        ->usingName('Gallery Image 1')
        ->usingFileName('gallery1.jpg')
        ->toMediaCollection('gallery');

    $newsPost->addMedia($image2->getRealPath())
        ->usingName('Gallery Image 2')
        ->usingFileName('gallery2.jpg')
        ->toMediaCollection('gallery');

    // Filter by name using array
    $filtered = $newsPost->getMedia('gallery', ['name' => 'Gallery Image 1']);

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->id)->toBe($media1->id);
});

it('can get media with deleted using callable filter', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('gallery1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('gallery2.jpg', 800, 600);

    $media1 = $newsPost->addMedia($image1->getRealPath())
        ->usingName('Gallery Image 1')
        ->usingFileName('gallery1.jpg')
        ->toMediaCollection('gallery');

    $newsPost->addMedia($image2->getRealPath())
        ->usingName('Gallery Image 2')
        ->usingFileName('gallery2.jpg')
        ->toMediaCollection('gallery');

    // Mark media1 as soft deleted via custom_properties
    $media1->custom_properties = ['deleted_at' => now()->toIso8601String()];
    $media1->save();

    // Get with deleted using callable filter
    $filtered = $newsPost->getMediaWithDeleted('gallery', fn ($m) => $m->name === 'Gallery Image 1');

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->id)->toBe($media1->id);
});

it('can get media with deleted using array filter', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('gallery1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('gallery2.jpg', 800, 600);

    $media1 = $newsPost->addMedia($image1->getRealPath())
        ->usingName('Gallery Image 1')
        ->usingFileName('gallery1.jpg')
        ->toMediaCollection('gallery');

    $newsPost->addMedia($image2->getRealPath())
        ->usingName('Gallery Image 2')
        ->usingFileName('gallery2.jpg')
        ->toMediaCollection('gallery');

    // Get with deleted using array filter
    $filtered = $newsPost->getMediaWithDeleted('gallery', ['name' => 'Gallery Image 1']);

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->id)->toBe($media1->id);
});

it('can force delete featured image', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('featured.jpg', 800, 600);

    $newsPost->addMedia($image->getRealPath())
        ->usingName('Featured Image')
        ->usingFileName('featured.jpg')
        ->toMediaCollection('featured');

    expect($newsPost->hasMedia('featured'))->toBeTrue();

    // Force delete the featured image
    $result = $newsPost->forceDeleteFeaturedImage();

    expect($result)->toBeTrue()
        ->and($newsPost->fresh()->hasMedia('featured'))->toBeFalse();
});

it('returns false when force deleting non-existent featured image', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    // No featured image exists
    $result = $newsPost->forceDeleteFeaturedImage();

    expect($result)->toBeFalse();
});

it('can soft delete featured image', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('featured.jpg', 800, 600);

    $newsPost->addMedia($image->getRealPath())
        ->usingName('Featured Image')
        ->usingFileName('featured.jpg')
        ->toMediaCollection('featured');

    expect($newsPost->hasMedia('featured'))->toBeTrue();

    // Soft delete the featured image
    $result = $newsPost->softDeleteFeaturedImage();

    expect($result)->toBeTrue()
        ->and($newsPost->hasMedia('featured'))->toBeFalse() // getMedia excludes soft-deleted
        ->and($newsPost->hasSoftDeletedFeaturedImages())->toBeTrue();
});

it('can restore soft deleted featured image', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('featured.jpg', 800, 600);

    $newsPost->addMedia($image->getRealPath())
        ->usingName('Featured Image')
        ->usingFileName('featured.jpg')
        ->toMediaCollection('featured');

    // Soft delete and then restore
    $newsPost->softDeleteFeaturedImage();
    expect($newsPost->hasMedia('featured'))->toBeFalse();

    $result = $newsPost->restoreFeaturedImage();

    expect($result)->toBeTrue()
        ->and($newsPost->hasMedia('featured'))->toBeTrue();
});

it('returns false when restoring non-existent soft deleted featured image', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    // No soft-deleted featured image exists
    $result = $newsPost->restoreFeaturedImage();

    expect($result)->toBeFalse();
});

it('can get soft deleted featured images', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('featured.jpg', 800, 600);

    $media = $newsPost->addMedia($image->getRealPath())
        ->usingName('Featured Image')
        ->usingFileName('featured.jpg')
        ->toMediaCollection('featured');

    // No soft-deleted images initially
    expect($newsPost->getSoftDeletedFeaturedImages())->toHaveCount(0);

    // Soft delete the featured image
    $newsPost->softDeleteFeaturedImage();

    $softDeleted = $newsPost->getSoftDeletedFeaturedImages();

    expect($softDeleted)->toHaveCount(1)
        ->and($softDeleted->first()->id)->toBe($media->id);
});

it('can force delete media by id', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('featured.jpg', 800, 600);

    $media = $newsPost->addMedia($image->getRealPath())
        ->usingName('Featured Image')
        ->usingFileName('featured.jpg')
        ->toMediaCollection('featured');

    $mediaId = $media->id;

    // Force delete the media by ID
    $result = $newsPost->forceDeleteMediaById($mediaId);

    expect($result)->toBeTrue()
        ->and($newsPost->hasMedia('featured'))->toBeFalse();
});

it('returns false when force deleting non-existent media by id', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $result = $newsPost->forceDeleteMediaById(99999);

    expect($result)->toBeFalse();
});

it('returns false when soft deleting non-existent featured image', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    // No featured image exists
    $result = $newsPost->softDeleteFeaturedImage();

    expect($result)->toBeFalse();
});

it('checks if media is soft deleted', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('featured.jpg', 800, 600);

    $media = $newsPost->addMedia($image->getRealPath())
        ->usingName('Featured Image')
        ->usingFileName('featured.jpg')
        ->toMediaCollection('featured');

    // Initially not soft deleted
    expect($newsPost->isMediaSoftDeleted($media))->toBeFalse();

    // Soft delete
    $newsPost->softDeleteFeaturedImage();
    $media->refresh();

    expect($newsPost->isMediaSoftDeleted($media))->toBeTrue();
});
