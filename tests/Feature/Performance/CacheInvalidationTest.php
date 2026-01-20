<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Public\Home;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\DocumentCategory;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Cache Invalidation Tests
|--------------------------------------------------------------------------
|
| These tests verify that model caches are correctly invalidated when
| data changes. This ensures that users see fresh data after updates.
|
*/

describe('Program Cache Invalidation', function () {
    it('caches active programs', function () {
        $program = Program::factory()->create(['is_active' => true]);

        // First call should cache
        $cachedPrograms = Program::getCachedActive();
        expect($cachedPrograms)->toHaveCount(1);

        // Verify cache exists
        expect(Cache::has('programs.active'))->toBeTrue();
    });

    it('invalidates cache when program is saved', function () {
        $program = Program::factory()->create(['is_active' => true]);

        // Populate cache
        Program::getCachedActive();
        expect(Cache::has('programs.active'))->toBeTrue();

        // Update program
        $program->update(['name' => 'Updated Name']);

        // Cache should be invalidated
        expect(Cache::has('programs.active'))->toBeFalse();

        // Fresh call should repopulate cache
        $cachedPrograms = Program::getCachedActive();
        expect($cachedPrograms->first()->name)->toBe('Updated Name');
    });

    it('invalidates cache when program is deleted', function () {
        $program = Program::factory()->create(['is_active' => true]);

        // Populate cache
        Program::getCachedActive();
        expect(Cache::has('programs.active'))->toBeTrue();

        // Delete program
        $program->delete();

        // Cache should be invalidated
        expect(Cache::has('programs.active'))->toBeFalse();
    });
});

describe('AcademicYear Cache Invalidation', function () {
    it('caches all academic years', function () {
        AcademicYear::factory()->count(3)->create();

        // First call should cache
        $cachedYears = AcademicYear::getCachedAll();
        expect($cachedYears)->toHaveCount(3);

        // Verify cache exists
        expect(Cache::has('academic_years.all'))->toBeTrue();
    });

    it('invalidates cache when academic year is saved', function () {
        $year = AcademicYear::factory()->create();

        // Populate cache
        AcademicYear::getCachedAll();
        expect(Cache::has('academic_years.all'))->toBeTrue();

        // Update academic year
        $year->update(['year' => '2099/2100']);

        // Cache should be invalidated
        expect(Cache::has('academic_years.all'))->toBeFalse();
    });

    it('invalidates cache when academic year is deleted', function () {
        $year = AcademicYear::factory()->create();

        // Populate cache
        AcademicYear::getCachedAll();
        expect(Cache::has('academic_years.all'))->toBeTrue();

        // Delete academic year
        $year->forceDelete();

        // Cache should be invalidated
        expect(Cache::has('academic_years.all'))->toBeFalse();
    });

    it('clears current academic year cache on update', function () {
        $year = AcademicYear::factory()->create(['is_current' => true]);

        // Populate current cache
        AcademicYear::getCurrent();
        expect(Cache::has('academic_year.current'))->toBeTrue();

        // Update - should clear both caches
        $year->update(['year' => '2099/2100']);

        // Both caches should be invalidated
        expect(Cache::has('academic_year.current'))->toBeFalse();
        expect(Cache::has('academic_years.all'))->toBeFalse();
    });
});

describe('DocumentCategory Cache Invalidation', function () {
    it('caches all document categories', function () {
        DocumentCategory::factory()->count(3)->create();

        // First call should cache
        $cachedCategories = DocumentCategory::getCachedAll();
        expect($cachedCategories)->toHaveCount(3);

        // Verify cache exists
        expect(Cache::has('document_categories.all'))->toBeTrue();
    });

    it('invalidates cache when category is saved', function () {
        $category = DocumentCategory::factory()->create();

        // Populate cache
        DocumentCategory::getCachedAll();
        expect(Cache::has('document_categories.all'))->toBeTrue();

        // Update category
        $category->update(['name' => 'Updated Category']);

        // Cache should be invalidated
        expect(Cache::has('document_categories.all'))->toBeFalse();
    });

    it('invalidates cache when category is deleted', function () {
        $category = DocumentCategory::factory()->create();

        // Populate cache
        DocumentCategory::getCachedAll();
        expect(Cache::has('document_categories.all'))->toBeTrue();

        // Delete category
        $category->delete();

        // Cache should be invalidated
        expect(Cache::has('document_categories.all'))->toBeFalse();
    });
});

describe('Home Page Cache Invalidation', function () {
    it('invalidates home cache when call is published', function () {
        $program = Program::factory()->create(['is_active' => true]);
        $year = AcademicYear::factory()->create(['is_current' => true]);

        // Create unpublished call
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $year->id,
            'status' => 'borrador',
            'published_at' => null,
        ]);

        // Populate home cache using the actual cache key from Home component
        Cache::put(Home::CACHE_KEY_CALLS, collect(), 900);
        expect(Cache::has(Home::CACHE_KEY_CALLS))->toBeTrue();

        // Publish call
        $call->update([
            'status' => 'abierta',
            'published_at' => now(),
        ]);

        // Home cache should be invalidated
        expect(Cache::has(Home::CACHE_KEY_CALLS))->toBeFalse();
    });

    it('invalidates home cache when news is published', function () {
        $program = Program::factory()->create(['is_active' => true]);
        $year = AcademicYear::factory()->create(['is_current' => true]);

        // Create unpublished news
        $news = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $year->id,
            'status' => 'borrador',
            'published_at' => null,
        ]);

        // Populate home cache using the actual cache key from Home component
        Cache::put(Home::CACHE_KEY_NEWS, collect(), 900);
        expect(Cache::has(Home::CACHE_KEY_NEWS))->toBeTrue();

        // Publish news
        $news->update([
            'status' => 'publicado',
            'published_at' => now(),
        ]);

        // Home cache should be invalidated
        expect(Cache::has(Home::CACHE_KEY_NEWS))->toBeFalse();
    });

    it('invalidates home cache when public event is created', function () {
        $program = Program::factory()->create(['is_active' => true]);

        // Populate home cache using the actual cache key from Home component
        Cache::put(Home::CACHE_KEY_EVENTS, collect(), 900);
        expect(Cache::has(Home::CACHE_KEY_EVENTS))->toBeTrue();

        // Create public event
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'is_public' => true,
            'start_date' => now()->addDays(5),
        ]);

        // Home cache should be invalidated
        expect(Cache::has(Home::CACHE_KEY_EVENTS))->toBeFalse();
    });

    it('does not invalidate home cache when draft call is updated', function () {
        $program = Program::factory()->create(['is_active' => true]);
        $year = AcademicYear::factory()->create(['is_current' => true]);

        // Create draft call
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $year->id,
            'status' => 'borrador',
            'published_at' => null,
        ]);

        // Populate home cache using the actual cache key from Home component
        Cache::put(Home::CACHE_KEY_CALLS, collect(), 900);
        expect(Cache::has(Home::CACHE_KEY_CALLS))->toBeTrue();

        // Update draft (but keep as draft)
        $call->update(['title' => 'Updated Title']);

        // Home cache should NOT be invalidated (status didn't change)
        expect(Cache::has(Home::CACHE_KEY_CALLS))->toBeTrue();
    });
});

describe('Dashboard Cache Invalidation', function () {
    it('clears all dashboard caches via clearCache method', function () {
        // Populate dashboard caches
        Cache::put('dashboard.statistics', [], 300);
        Cache::put('dashboard.charts.monthly_activity', [], 900);
        Cache::put('dashboard.charts.calls_by_program', [], 900);
        Cache::put('dashboard.charts.calls_by_status', [], 900);
        Cache::put('dashboard.alerts', collect(), 300);
        Cache::put('dashboard.recent_activities', collect(), 120);

        // Verify all caches exist
        expect(Cache::has('dashboard.statistics'))->toBeTrue();
        expect(Cache::has('dashboard.charts.monthly_activity'))->toBeTrue();
        expect(Cache::has('dashboard.charts.calls_by_program'))->toBeTrue();
        expect(Cache::has('dashboard.charts.calls_by_status'))->toBeTrue();
        expect(Cache::has('dashboard.alerts'))->toBeTrue();
        expect(Cache::has('dashboard.recent_activities'))->toBeTrue();

        // Clear all caches
        Dashboard::clearCache();

        // All caches should be invalidated
        expect(Cache::has('dashboard.statistics'))->toBeFalse();
        expect(Cache::has('dashboard.charts.monthly_activity'))->toBeFalse();
        expect(Cache::has('dashboard.charts.calls_by_program'))->toBeFalse();
        expect(Cache::has('dashboard.charts.calls_by_status'))->toBeFalse();
        expect(Cache::has('dashboard.alerts'))->toBeFalse();
        expect(Cache::has('dashboard.recent_activities'))->toBeFalse();
    });
});

describe('Home Component Cache', function () {
    it('clears all home caches via clearCache method', function () {
        // Populate home caches using actual constants from Home component
        Cache::put(Home::CACHE_KEY_CALLS, collect(), 900);
        Cache::put(Home::CACHE_KEY_NEWS, collect(), 900);
        Cache::put(Home::CACHE_KEY_EVENTS, collect(), 900);

        // Verify all caches exist
        expect(Cache::has(Home::CACHE_KEY_CALLS))->toBeTrue();
        expect(Cache::has(Home::CACHE_KEY_NEWS))->toBeTrue();
        expect(Cache::has(Home::CACHE_KEY_EVENTS))->toBeTrue();

        // Clear all caches
        Home::clearCache();

        // All caches should be invalidated
        expect(Cache::has(Home::CACHE_KEY_CALLS))->toBeFalse();
        expect(Cache::has(Home::CACHE_KEY_NEWS))->toBeFalse();
        expect(Cache::has(Home::CACHE_KEY_EVENTS))->toBeFalse();
    });
});
