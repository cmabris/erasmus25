<?php

use App\Models\Call;
use App\Models\Language;
use App\Models\Program;
use App\Models\Translation;

it('belongs to a language', function () {
    $language = Language::factory()->create();
    $program = Program::factory()->create();
    $translation = Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => Program::class,
        'translatable_id' => $program->id,
    ]);

    expect($translation->language)->toBeInstanceOf(Language::class)
        ->and($translation->language->id)->toBe($language->id);
});

it('belongs to a polymorphic translatable model', function () {
    $language = Language::factory()->create();
    $program = Program::factory()->create();
    $translation = Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => Program::class,
        'translatable_id' => $program->id,
        'field' => 'name',
        'value' => 'Education Program',
    ]);

    expect($translation->translatable)->toBeInstanceOf(Program::class)
        ->and($translation->translatable->id)->toBe($program->id);
});

it('can reference different model types polymorphically', function () {
    $language = Language::factory()->create();
    $program = Program::factory()->create();
    $academicYear = \App\Models\AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $translation1 = Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => Program::class,
        'translatable_id' => $program->id,
        'field' => 'name',
    ]);
    $translation2 = Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => Call::class,
        'translatable_id' => $call->id,
        'field' => 'title',
    ]);

    expect($translation1->translatable)->toBeInstanceOf(Program::class)
        ->and($translation2->translatable)->toBeInstanceOf(Call::class);
});

it('is deleted in cascade when language is deleted', function () {
    $language = Language::factory()->create();
    $program = Program::factory()->create();
    $translation = Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => Program::class,
        'translatable_id' => $program->id,
    ]);

    $language->delete();

    expect(Translation::find($translation->id))->toBeNull();
});

it('maintains translatable reference when model is deleted', function () {
    $language = Language::factory()->create();
    $program = Program::factory()->create();
    $translation = Translation::factory()->create([
        'language_id' => $language->id,
        'translatable_type' => Program::class,
        'translatable_id' => $program->id,
    ]);

    $program->delete();
    $translation->refresh();

    // La traducción mantiene la referencia aunque el modelo haya sido eliminado
    expect($translation->translatable_type)->toBe(Program::class)
        ->and($translation->translatable_id)->toBe($program->id);
});
