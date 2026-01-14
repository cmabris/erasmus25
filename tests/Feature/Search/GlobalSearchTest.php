<?php

use App\Livewire\Search\GlobalSearch;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Global Search Component', function () {
    beforeEach(function () {
        // Create test data
        $this->program = Program::factory()->create([
            'name' => 'Programa de Movilidad',
            'code' => 'KA1',
            'description' => 'Programa de movilidad estudiantil',
            'is_active' => true,
        ]);

        $this->academicYear = AcademicYear::factory()->create([
            'year' => '2024-2025',
        ]);

        $this->call = Call::factory()->create([
            'title' => 'Convocatoria de Movilidad',
            'requirements' => 'Requisitos para movilidad',
            'status' => 'abierta',
            'published_at' => now(),
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->news = NewsPost::factory()->create([
            'title' => 'Noticia sobre Movilidad',
            'excerpt' => 'Resumen de la noticia',
            'status' => 'publicado',
            'published_at' => now(),
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->document = Document::factory()->create([
            'title' => 'Documento de Movilidad',
            'description' => 'Descripción del documento',
            'is_active' => true,
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
    });

    it('renders the global search component', function () {
        Livewire::test(GlobalSearch::class)
            ->assertSuccessful()
            ->assertSee(__('common.search.global_title'))
            ->assertSee(__('common.search.global_description'));
    });

    it('shows initial state when no query is provided', function () {
        Livewire::test(GlobalSearch::class)
            ->assertSee(__('common.search.start_search'))
            ->assertSee(__('common.search.start_search_message'));
    });

    it('searches in programs', function () {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'Movilidad')
            ->set('types', ['programs'])
            ->assertSee('Programa de Movilidad')
            ->assertSee(__('common.search.programs'));
    });

    it('searches in calls', function () {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'Convocatoria')
            ->set('types', ['calls'])
            ->assertSee('Convocatoria de Movilidad')
            ->assertSee(__('common.search.calls'));
    });

    it('searches in news', function () {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'Noticia')
            ->set('types', ['news'])
            ->assertSee('Noticia sobre Movilidad')
            ->assertSee(__('common.search.news'));
    });

    it('searches in documents', function () {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'Documento')
            ->set('types', ['documents'])
            ->assertSee('Documento de Movilidad')
            ->assertSee(__('common.search.documents'));
    });

    it('searches across all types by default', function () {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'Movilidad')
            ->assertSee('Programa de Movilidad')
            ->assertSee('Convocatoria de Movilidad')
            ->assertSee('Noticia sobre Movilidad')
            ->assertSee('Documento de Movilidad');
    });

    it('shows no results message when query has no matches', function () {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'NoExisteEsteTermino')
            ->assertSee(__('common.search.no_results'))
            ->assertSee(__('common.search.no_results_message'));
    });

    it('filters by program', function () {
        $otherProgram = Program::factory()->create([
            'name' => 'Otro Programa',
            'is_active' => true,
        ]);

        $otherCall = Call::factory()->create([
            'title' => 'Otra Convocatoria',
            'status' => 'abierta',
            'published_at' => now(),
            'program_id' => $otherProgram->id,
        ]);

        Livewire::test(GlobalSearch::class)
            ->set('query', 'Convocatoria')
            ->set('program', $this->program->id)
            ->assertSee('Convocatoria de Movilidad')
            ->assertDontSee('Otra Convocatoria');
    });

    it('filters by academic year', function () {
        $otherYear = AcademicYear::factory()->create([
            'year' => '2023-2024',
        ]);

        $otherCall = Call::factory()->create([
            'title' => 'Otra Convocatoria',
            'status' => 'abierta',
            'published_at' => now(),
            'program_id' => $this->program->id,
            'academic_year_id' => $otherYear->id,
        ]);

        Livewire::test(GlobalSearch::class)
            ->set('query', 'Convocatoria')
            ->set('academicYear', $this->academicYear->id)
            ->assertSee('Convocatoria de Movilidad')
            ->assertDontSee('Otra Convocatoria');
    });

    it('can toggle content types', function () {
        $component = Livewire::test(GlobalSearch::class)
            ->assertSet('types', ['programs', 'calls', 'news', 'documents']);

        $component->call('toggleType', 'programs')
            ->assertSet('types', ['calls', 'news', 'documents']);

        $component->call('toggleType', 'programs')
            ->assertSet('types', ['calls', 'news', 'documents', 'programs']);
    });

    it('can toggle filters panel', function () {
        $component = Livewire::test(GlobalSearch::class)
            ->assertSet('showFilters', false);

        $component->call('toggleFilters')
            ->assertSet('showFilters', true);

        $component->call('toggleFilters')
            ->assertSet('showFilters', false);
    });

    it('can reset all filters', function () {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'Movilidad')
            ->set('program', $this->program->id)
            ->set('academicYear', $this->academicYear->id)
            ->set('showFilters', true)
            ->call('resetFilters')
            ->assertSet('query', '')
            ->assertSet('program', null)
            ->assertSet('academicYear', null)
            ->assertSet('showFilters', false)
            ->assertSet('types', ['programs', 'calls', 'news', 'documents']);
    });

    it('only shows active programs', function () {
        $inactiveProgram = Program::factory()->create([
            'name' => 'Programa Inactivo',
            'is_active' => false,
        ]);

        Livewire::test(GlobalSearch::class)
            ->set('query', 'Programa')
            ->set('types', ['programs'])
            ->assertSee('Programa de Movilidad')
            ->assertDontSee('Programa Inactivo');
    });

    it('only shows published calls', function () {
        $unpublishedCall = Call::factory()->create([
            'title' => 'Convocatoria No Publicada',
            'status' => 'abierta',
            'published_at' => null,
        ]);

        Livewire::test(GlobalSearch::class)
            ->set('query', 'Convocatoria')
            ->set('types', ['calls'])
            ->assertSee('Convocatoria de Movilidad')
            ->assertDontSee('Convocatoria No Publicada');
    });

    it('only shows published news', function () {
        $draftNews = NewsPost::factory()->create([
            'title' => 'Noticia Borrador',
            'status' => 'borrador',
            'published_at' => null,
        ]);

        Livewire::test(GlobalSearch::class)
            ->set('query', 'Noticia')
            ->set('types', ['news'])
            ->assertSee('Noticia sobre Movilidad')
            ->assertDontSee('Noticia Borrador');
    });

    it('only shows active documents', function () {
        $inactiveDocument = Document::factory()->create([
            'title' => 'Documento Inactivo',
            'is_active' => false,
        ]);

        Livewire::test(GlobalSearch::class)
            ->set('query', 'Documento')
            ->set('types', ['documents'])
            ->assertSee('Documento de Movilidad')
            ->assertDontSee('Documento Inactivo');
    });

    it('limits results per type', function () {
        // Create more than limitPerType (10) programs
        Program::factory()->count(15)->create([
            'name' => 'Programa Test',
            'is_active' => true,
        ]);

        $component = Livewire::test(GlobalSearch::class)
            ->set('query', 'Programa Test')
            ->set('types', ['programs']);

        $results = $component->get('results');
        expect($results['programs'])->toHaveCount(10);
    });

    it('displays total results count', function () {
        Livewire::test(GlobalSearch::class)
            ->set('query', 'Movilidad')
            ->assertSee(__('common.search.results_found', ['total' => 4]));
    });
});

describe('Global Search Route', function () {
    it('can access the search route', function () {
        $this->get(route('search'))
            ->assertSuccessful()
            ->assertSeeLivewire(GlobalSearch::class);
    });
});

describe('Global Search Context Detection', function () {
    it('uses public routes when accessed from public area', function () {
        $program = Program::factory()->create([
            'name' => 'Programa Test',
            'is_active' => true,
        ]);

        Livewire::test(GlobalSearch::class)
            ->set('query', 'Programa Test')
            ->set('types', ['programs'])
            ->assertSee(route('programas.show', $program));
    });

    it('uses admin routes when accessed with admin parameter', function () {
        $user = \App\Models\User::factory()->create();
        
        // Create permission if it doesn't exist
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'programs.view', 'guard_name' => 'web']);
        $user->givePermissionTo('programs.view');

        $program = Program::factory()->create([
            'name' => 'Programa Test',
            'is_active' => true,
        ]);

        // Test with admin parameter passed to component
        Livewire::actingAs($user)
            ->test(GlobalSearch::class, ['admin' => true])
            ->set('query', 'Programa Test')
            ->set('types', ['programs'])
            ->assertSee(route('admin.programs.show', $program));
    });

    it('detects admin context from route parameter', function () {
        $component = Livewire::test(GlobalSearch::class, ['admin' => true]);
        
        expect($component->get('isAdminContext'))->toBeTrue();
    });

    it('detects public context by default', function () {
        $component = Livewire::test(GlobalSearch::class);
        
        expect($component->get('isAdminContext'))->toBeFalse();
    });
});
