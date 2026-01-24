<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Tests\Browser\Helpers\createGlobalSearchTestData;
use function Tests\Browser\Helpers\createNewsletterTestData;

uses(RefreshDatabase::class);

it('createNewsletterTestData creates programs with known codes', function () {
    $data = createNewsletterTestData();

    expect($data)->toHaveKey('programs')
        ->and($data['programs'])->toHaveCount(3);

    $codes = $data['programs']->pluck('code')->all();
    expect($codes)->toContain('KA1', 'KA2', 'KA3');

    $this->assertDatabaseHas('programs', ['code' => 'KA1', 'is_active' => true]);
    $this->assertDatabaseHas('programs', ['code' => 'KA2', 'is_active' => true]);
    $this->assertDatabaseHas('programs', ['code' => 'KA3', 'is_active' => true]);
});

it('createGlobalSearchTestData creates searchable content with Movilidad', function () {
    $data = createGlobalSearchTestData();

    expect($data)
        ->toHaveKeys(['program', 'academicYear', 'call', 'news', 'document']);

    $this->assertDatabaseHas('programs', ['name' => 'Programa de Movilidad']);
    $this->assertDatabaseHas('calls', ['title' => 'Convocatoria de Movilidad', 'status' => 'abierta']);
    $this->assertDatabaseHas('news_posts', ['title' => 'Noticia sobre Movilidad', 'status' => 'publicado']);
    $this->assertDatabaseHas('documents', ['title' => 'Documento de Movilidad', 'is_active' => true]);
});
