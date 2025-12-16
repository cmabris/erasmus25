<?php

use App\Models\Language;
use App\Models\Translation;

it('has many translations', function () {
    $language = Language::factory()->create();
    $program = \App\Models\Program::factory()->create();
    Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => \App\Models\Program::class,
        'translatable_id' => $program->id,
        'field' => 'name',
    ]);
    Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => \App\Models\Program::class,
        'translatable_id' => $program->id,
        'field' => 'description',
    ]);
    Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => \App\Models\Program::class,
        'translatable_id' => $program->id,
        'field' => 'slug',
    ]);

    expect($language->translations)->toHaveCount(3)
        ->and($language->translations->first())->toBeInstanceOf(Translation::class);
});

it('deletes translations in cascade when language is deleted', function () {
    $language = Language::factory()->create();
    $program = \App\Models\Program::factory()->create(['code' => 'KA997', 'name' => 'Programa Test 3', 'slug' => 'programa-test-3']);
    $translation1 = Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => \App\Models\Program::class,
        'translatable_id' => $program->id,
        'field' => 'name',
    ]);
    $translation2 = Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => \App\Models\Program::class,
        'translatable_id' => $program->id,
        'field' => 'description',
    ]);

    $language->delete();

    expect(Translation::find($translation1->id))->toBeNull()
        ->and(Translation::find($translation2->id))->toBeNull();
});

