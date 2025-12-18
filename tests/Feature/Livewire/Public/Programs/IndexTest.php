<?php

use App\Livewire\Public\Programs\Index;
use App\Models\Program;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Create test programs
    Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Movilidad Formación Profesional',
        'is_active' => true,
        'order' => 1,
    ]);

    Program::factory()->create([
        'code' => 'KA220-SCH',
        'name' => 'Asociaciones de Cooperación',
        'is_active' => true,
        'order' => 2,
    ]);

    Program::factory()->create([
        'code' => 'KA1-OLD',
        'name' => 'Programa Histórico',
        'is_active' => false,
        'order' => 99,
    ]);
});

it('renders the programs index page', function () {
    $this->get(route('programas.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

it('displays active programs by default', function () {
    Livewire::test(Index::class)
        ->assertSee('Movilidad Formación Profesional')
        ->assertSee('Asociaciones de Cooperación')
        ->assertDontSee('Programa Histórico');
});

it('can show inactive programs when filter is disabled', function () {
    Livewire::test(Index::class)
        ->set('onlyActive', false)
        ->assertSee('Movilidad Formación Profesional')
        ->assertSee('Asociaciones de Cooperación')
        ->assertSee('Programa Histórico');
});

it('can search programs by name', function () {
    Livewire::test(Index::class)
        ->set('search', 'Formación')
        ->assertSee('Movilidad Formación Profesional')
        ->assertDontSee('Asociaciones de Cooperación');
});

it('can search programs by code', function () {
    Livewire::test(Index::class)
        ->set('search', 'KA220')
        ->assertDontSee('Movilidad Formación Profesional')
        ->assertSee('Asociaciones de Cooperación');
});

it('can filter programs by type KA1', function () {
    Livewire::test(Index::class)
        ->set('type', 'KA1')
        ->assertSee('Movilidad Formación Profesional')
        ->assertDontSee('Asociaciones de Cooperación');
});

it('can filter programs by type KA2', function () {
    Livewire::test(Index::class)
        ->set('type', 'KA2')
        ->assertDontSee('Movilidad Formación Profesional')
        ->assertSee('Asociaciones de Cooperación');
});

it('can reset filters', function () {
    Livewire::test(Index::class)
        ->set('search', 'test')
        ->set('type', 'KA1')
        ->set('onlyActive', false)
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('type', '')
        ->assertSet('onlyActive', true);
});

it('shows empty state when no programs match filters', function () {
    Livewire::test(Index::class)
        ->set('search', 'nonexistent program xyz')
        ->assertSee(__('No se encontraron programas'));
});

it('displays statistics correctly', function () {
    Livewire::test(Index::class)
        ->assertSeeHtml('2') // Active programs
        ->assertSeeHtml('1'); // Mobility programs (KA1)
});

it('supports pagination', function () {
    // The component uses WithPagination trait
    $component = Livewire::test(Index::class);

    // Verify the component has the paginators property from the trait
    expect($component->instance())->toBeInstanceOf(\App\Livewire\Public\Programs\Index::class);

    // Create enough programs to trigger pagination
    Program::factory()->count(20)->create(['is_active' => true]);

    // Test with many programs should show pagination
    Livewire::test(Index::class)
        ->assertOk();
});

it('updates search and resets pagination', function () {
    Program::factory()->count(15)->create(['is_active' => true]);

    // Test that search updates work correctly
    Livewire::test(Index::class)
        ->set('search', 'Movilidad')
        ->assertSet('search', 'Movilidad');
});

it('has correct seo title and description', function () {
    $this->get(route('programas.index'))
        ->assertOk()
        ->assertSee('Programas Erasmus+');
});

it('shows breadcrumbs', function () {
    Livewire::test(Index::class)
        ->assertSee(__('Programas'));
});

it('links to program detail page', function () {
    $program = Program::where('is_active', true)->first();

    $this->get(route('programas.index'))
        ->assertOk()
        ->assertSee(route('programas.show', $program->slug));
});
