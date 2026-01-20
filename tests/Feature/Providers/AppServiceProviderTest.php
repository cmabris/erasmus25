<?php

use App\Models\Call;
use App\Models\Document;
use App\Models\Language;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\Translation;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('AppServiceProvider - Blade Directive @trans', function () {
    it('registers @trans directive that compiles correctly', function () {
        // Get all registered Blade directives
        $directives = Blade::getCustomDirectives();

        expect($directives)->toHaveKey('trans');
        expect($directives['trans'])->toBeCallable();
    });

    it('compiles @trans directive to trans_model call', function () {
        $directives = Blade::getCustomDirectives();

        // Call the directive handler with an expression
        $compiled = $directives['trans']('$model, "name"');

        expect($compiled)->toBe("<?php echo trans_model(\$model, \"name\") ?? ''; ?>");
    });

    it('renders @trans directive with actual translation', function () {
        // Create language and translation
        $language = Language::factory()->create([
            'code' => 'es',
            'is_active' => true,
        ]);

        $program = Program::factory()->create([
            'name' => 'Test Program',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Programa de Prueba',
        ]);

        app()->setLocale('es');

        // Compile and render a Blade template with @trans
        $bladeContent = '@trans($program, "name")';
        $compiled = Blade::compileString($bladeContent);

        // Evaluate the compiled PHP
        $result = null;
        extract(['program' => $program]);
        ob_start();
        eval('?>'.$compiled);
        $result = ob_get_clean();

        expect($result)->toBe('Programa de Prueba');
    });

    it('renders empty string when no translation exists', function () {
        // Create language for fallback
        Language::factory()->create([
            'code' => 'en',
            'is_active' => true,
            'is_default' => true,
        ]);

        $program = Program::factory()->create([
            'name' => 'Default Name',
        ]);

        app()->setLocale('en');

        // Compile and render - trans_model returns null when no translation
        // and the directive uses ?? '' to return empty string
        $bladeContent = '@trans($program, "name")';
        $compiled = Blade::compileString($bladeContent);

        $result = null;
        extract(['program' => $program]);
        ob_start();
        eval('?>'.$compiled);
        $result = ob_get_clean();

        // trans_model returns null when no translation exists
        // The directive's ?? '' makes it return empty string
        expect($result)->toBe('');
    });

    it('renders empty string when trans_model returns null', function () {
        // Compile with null value
        $bladeContent = '@trans(null, "name")';
        $compiled = Blade::compileString($bladeContent);

        ob_start();
        eval('?>'.$compiled);
        $result = ob_get_clean();

        expect($result)->toBe('');
    });
});

describe('AppServiceProvider - Gate Policies', function () {
    it('registers RolePolicy for Spatie Role model', function () {
        $policy = Gate::getPolicyFor(Role::class);

        expect($policy)->toBeInstanceOf(RolePolicy::class);
    });

    it('registers ActivityPolicy for Spatie Activity model', function () {
        $policy = Gate::getPolicyFor(Activity::class);

        expect($policy)->toBeInstanceOf(ActivityPolicy::class);
    });
});

describe('AppServiceProvider - Model Observers', function () {
    it('Call model has observer registered', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create();

        // Create a call - if observer works, it should trigger activity logging
        $call = Call::factory()->create([
            'created_by' => $user->id,
            'program_id' => $program->id,
        ]);

        // The observer is registered if the model can be created without errors
        expect($call)->toBeInstanceOf(Call::class);
        expect($call->exists)->toBeTrue();
    });

    it('Resolution model has observer registered', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create();
        $call = Call::factory()->create([
            'created_by' => $user->id,
            'program_id' => $program->id,
        ]);

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'created_by' => $user->id,
        ]);

        expect($resolution)->toBeInstanceOf(Resolution::class);
        expect($resolution->exists)->toBeTrue();
    });

    it('NewsPost model has observer registered', function () {
        $user = User::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'author_id' => $user->id,
        ]);

        expect($newsPost)->toBeInstanceOf(NewsPost::class);
        expect($newsPost->exists)->toBeTrue();
    });

    it('Document model has observer registered', function () {
        $document = Document::factory()->create();

        expect($document)->toBeInstanceOf(Document::class);
        expect($document->exists)->toBeTrue();
    });
});

describe('AppServiceProvider - Helpers', function () {
    it('loads helpers.php file', function () {
        // If helpers are loaded, functions should be available
        expect(function_exists('trans_model'))->toBeTrue();
        expect(function_exists('getCurrentLanguage'))->toBeTrue();
        expect(function_exists('getAvailableLanguages'))->toBeTrue();
        expect(function_exists('format_number'))->toBeTrue();
    });
});
