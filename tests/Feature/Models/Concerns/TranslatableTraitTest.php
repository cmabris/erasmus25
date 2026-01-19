<?php

use App\Models\Language;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Translatable Trait Tests
|--------------------------------------------------------------------------
|
| Tests para el trait Translatable usado por modelos como Program, Setting, etc.
| Objetivo: Aumentar cobertura de 60.87% a 90%+
|
*/

beforeEach(function () {
    $this->language = Language::factory()->create([
        'code' => 'en',
        'is_active' => true,
    ]);

    $this->spanishLanguage = Language::factory()->create([
        'code' => 'es',
        'is_active' => true,
        'is_default' => true,
    ]);

    App::setLocale('en');
});

describe('translations relationship', function () {
    it('has morphMany translations relationship', function () {
        $program = Program::factory()->create();

        expect($program->translations())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
    });

    it('can have multiple translations', function () {
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'English Name',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->spanishLanguage->id,
            'field' => 'name',
            'value' => 'Nombre en Español',
        ]);

        expect($program->translations)->toHaveCount(2);
    });
});

describe('translate method', function () {
    it('returns translation for specific field and current locale', function () {
        $program = Program::factory()->create(['name' => 'Original Name']);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'Translated Name',
        ]);

        App::setLocale('en');

        $result = $program->translate('name');

        expect($result)->toBe('Translated Name');
    });

    it('returns translation for specific locale', function () {
        $program = Program::factory()->create(['name' => 'Original Name']);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->spanishLanguage->id,
            'field' => 'name',
            'value' => 'Nombre Traducido',
        ]);

        $result = $program->translate('name', 'es');

        expect($result)->toBe('Nombre Traducido');
    });

    it('returns null when translation not found', function () {
        $program = Program::factory()->create();

        $result = $program->translate('name');

        expect($result)->toBeNull();
    });
});

describe('getTranslationsForLocale method', function () {
    it('returns all translations for current locale', function () {
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'English Name',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'description',
            'value' => 'English Description',
        ]);

        App::setLocale('en');

        $translations = $program->getTranslationsForLocale();

        expect($translations)->toHaveCount(2)
            ->and($translations->has('name'))->toBeTrue()
            ->and($translations->has('description'))->toBeTrue()
            ->and($translations->get('name')->value)->toBe('English Name');
    });

    it('returns translations for specific locale', function () {
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->spanishLanguage->id,
            'field' => 'name',
            'value' => 'Nombre en Español',
        ]);

        $translations = $program->getTranslationsForLocale('es');

        expect($translations)->toHaveCount(1)
            ->and($translations->get('name')->value)->toBe('Nombre en Español');
    });

    it('returns empty collection when no translations exist', function () {
        $program = Program::factory()->create();

        $translations = $program->getTranslationsForLocale('en');

        expect($translations)->toBeEmpty();
    });
});

describe('setTranslation method', function () {
    it('creates new translation', function () {
        $program = Program::factory()->create();

        $translation = $program->setTranslation('name', 'en', 'New English Name');

        expect($translation)->toBeInstanceOf(Translation::class)
            ->and($translation->field)->toBe('name')
            ->and($translation->value)->toBe('New English Name')
            ->and($program->translations)->toHaveCount(1);
    });

    it('updates existing translation', function () {
        $program = Program::factory()->create();

        // Create initial translation
        $program->setTranslation('name', 'en', 'Initial Name');

        // Update translation
        $translation = $program->setTranslation('name', 'en', 'Updated Name');

        expect($translation->value)->toBe('Updated Name')
            ->and($program->fresh()->translations)->toHaveCount(1);
    });

    it('throws exception for inactive language', function () {
        $inactiveLanguage = Language::factory()->create([
            'code' => 'fr',
            'is_active' => false,
        ]);

        $program = Program::factory()->create();

        expect(fn () => $program->setTranslation('name', 'fr', 'French Name'))
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

describe('hasTranslation method', function () {
    it('returns true when translation exists', function () {
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'English Name',
        ]);

        App::setLocale('en');

        expect($program->hasTranslation('name'))->toBeTrue();
    });

    it('returns false when translation does not exist', function () {
        $program = Program::factory()->create();

        App::setLocale('en');

        expect($program->hasTranslation('name'))->toBeFalse();
    });

    it('checks translation for specific locale', function () {
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->spanishLanguage->id,
            'field' => 'name',
            'value' => 'Nombre',
        ]);

        expect($program->hasTranslation('name', 'es'))->toBeTrue()
            ->and($program->hasTranslation('name', 'en'))->toBeFalse();
    });
});

describe('getTranslatedAttribute method', function () {
    it('returns translated attribute value', function () {
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'Translated Name',
        ]);

        App::setLocale('en');

        $result = $program->getTranslatedAttribute('name');

        expect($result)->toBe('Translated Name');
    });

    it('returns null when no translation exists', function () {
        $program = Program::factory()->create();

        App::setLocale('en');

        $result = $program->getTranslatedAttribute('name');

        expect($result)->toBeNull();
    });
});

describe('deleteTranslations method', function () {
    it('deletes all translations for model', function () {
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'English Name',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->spanishLanguage->id,
            'field' => 'name',
            'value' => 'Nombre',
        ]);

        expect($program->translations)->toHaveCount(2);

        $program->deleteTranslations();

        expect($program->fresh()->translations)->toHaveCount(0);
    });
});

describe('deleteTranslation method', function () {
    it('deletes translation for specific field in all locales', function () {
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'English Name',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->spanishLanguage->id,
            'field' => 'name',
            'value' => 'Nombre',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'description',
            'value' => 'Description',
        ]);

        $program->deleteTranslation('name');

        $remaining = $program->fresh()->translations;

        expect($remaining)->toHaveCount(1)
            ->and($remaining->first()->field)->toBe('description');
    });

    it('deletes translation for specific field and locale', function () {
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'English Name',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->spanishLanguage->id,
            'field' => 'name',
            'value' => 'Nombre',
        ]);

        $program->deleteTranslation('name', 'en');

        $remaining = $program->fresh()->translations;

        expect($remaining)->toHaveCount(1)
            ->and($remaining->first()->language_id)->toBe($this->spanishLanguage->id);
    });
});

describe('translateOr method', function () {
    it('returns translation when exists', function () {
        $program = Program::factory()->create(['name' => 'Original Name']);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'Translated Name',
        ]);

        App::setLocale('en');

        $result = $program->translateOr('name', $program->name);

        expect($result)->toBe('Translated Name');
    });

    it('returns fallback when translation not found', function () {
        $program = Program::factory()->create(['name' => 'Original Name']);

        App::setLocale('en');

        $result = $program->translateOr('name', $program->name);

        expect($result)->toBe('Original Name');
    });

    it('returns fallback for specific locale without translation', function () {
        $program = Program::factory()->create(['name' => 'Original Name']);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'English Name',
        ]);

        // No Spanish translation
        $result = $program->translateOr('name', $program->name, 'es');

        expect($result)->toBe('Original Name');
    });
});

describe('bootTranslatable', function () {
    it('boots without errors', function () {
        // The bootTranslatable method is called automatically when using the trait
        // Just verify a model using the trait can be created without issues
        $program = Program::factory()->create();

        expect($program)->toBeInstanceOf(Program::class);
    });
});
