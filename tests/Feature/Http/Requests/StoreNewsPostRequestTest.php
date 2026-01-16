<?php

use App\Http\Requests\StoreNewsPostRequest;
use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::NEWS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_DELETE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_PUBLISH, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
        Permissions::NEWS_DELETE,
        Permissions::NEWS_PUBLISH,
    ]);

    // Editor tiene permisos limitados (sin publish ni delete)
    $editor->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
    ]);
});

describe('StoreNewsPostRequest - Authorization', function () {
    it('authorizes user with create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create(
            '/admin/noticias',
            'POST',
            [
                'academic_year_id' => $academicYear->id,
                'title' => 'Test News',
                'content' => 'Test content',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create(
            '/admin/noticias',
            'POST',
            [
                'academic_year_id' => $academicYear->id,
                'title' => 'Test News',
                'content' => 'Test content',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes editor user with create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create(
            '/admin/noticias',
            'POST',
            [
                'academic_year_id' => $academicYear->id,
                'title' => 'Test News',
                'content' => 'Test content',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('denies viewer user without create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create(
            '/admin/noticias',
            'POST',
            [
                'academic_year_id' => $academicYear->id,
                'title' => 'Test News',
                'content' => 'Test content',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create(
            '/admin/noticias',
            'POST',
            [
                'academic_year_id' => $academicYear->id,
                'title' => 'Test News',
                'content' => 'Test content',
            ]
        );
        $request->setUserResolver(fn () => null);

        expect($request->authorize())->toBeFalse();
    });
});

describe('StoreNewsPostRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('academic_year_id'))->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
        expect($validator->errors()->has('content'))->toBeTrue();
    });

    it('validates academic_year_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'academic_year_id' => 99999,
            'title' => 'Test News',
            'content' => 'Test content',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('academic_year_id'))->toBeTrue();
    });

    it('validates program_id exists when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'academic_year_id' => $academicYear->id,
            'program_id' => 99999,
            'title' => 'Test News',
            'content' => 'Test content',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('program_id'))->toBeTrue();
    });

    it('validates slug uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        NewsPost::factory()->create(['slug' => 'existing-slug']);

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'academic_year_id' => $academicYear->id,
            'title' => 'Test News',
            'content' => 'Test content',
            'slug' => 'existing-slug', // Ya existe
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('validates mobility_type enum', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'academic_year_id' => $academicYear->id,
            'title' => 'Test News',
            'content' => 'Test content',
            'mobility_type' => 'invalid-type',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('mobility_type'))->toBeTrue();
    });

    it('validates mobility_category enum', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'academic_year_id' => $academicYear->id,
            'title' => 'Test News',
            'content' => 'Test content',
            'mobility_category' => 'invalid-category',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('mobility_category'))->toBeTrue();
    });

    it('validates status enum', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'academic_year_id' => $academicYear->id,
            'title' => 'Test News',
            'content' => 'Test content',
            'status' => 'invalid-status',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('status'))->toBeTrue();
    });

    it('validates featured_image file type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $validator = Validator::make([
            'academic_year_id' => $academicYear->id,
            'title' => 'Test News',
            'content' => 'Test content',
            'featured_image' => $file,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('featured_image'))->toBeTrue();
    });

    it('validates featured_image file size', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        // Crear un archivo de más de 5MB (5120 KB)
        $file = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB

        $validator = Validator::make([
            'academic_year_id' => $academicYear->id,
            'title' => 'Test News',
            'content' => 'Test content',
            'featured_image' => $file,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('featured_image'))->toBeTrue();
    });

    it('validates tags array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'academic_year_id' => $academicYear->id,
            'title' => 'Test News',
            'content' => 'Test content',
            'tags' => 'not-an-array',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('tags'))->toBeTrue();
    });

    it('validates tags exist', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'academic_year_id' => $academicYear->id,
            'title' => 'Test News',
            'content' => 'Test content',
            'tags' => [99999, 99998], // No existen
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('tags.0'))->toBeTrue();
    });

    it('accepts valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();
        $author = User::factory()->create();
        $reviewer = User::factory()->create();
        $tag1 = NewsTag::factory()->create();
        $tag2 = NewsTag::factory()->create();
        $file = UploadedFile::fake()->image('news.jpg');

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Test News',
            'slug' => 'test-news',
            'excerpt' => 'Test excerpt',
            'content' => 'Test content',
            'country' => 'España',
            'city' => 'Madrid',
            'host_entity' => 'Test Entity',
            'mobility_type' => 'alumnado',
            'mobility_category' => 'FCT',
            'status' => 'borrador',
            'published_at' => '2024-01-01',
            'author_id' => $author->id,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => '2024-01-02',
            'featured_image' => $file,
            'tags' => [$tag1->id, $tag2->id],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('StoreNewsPostRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('academic_year_id.required');
        expect($messages)->toHaveKey('academic_year_id.exists');
        expect($messages)->toHaveKey('program_id.exists');
        expect($messages)->toHaveKey('title.required');
        expect($messages)->toHaveKey('title.max');
        expect($messages)->toHaveKey('slug.unique');
        expect($messages)->toHaveKey('content.required');
        expect($messages)->toHaveKey('country.max');
        expect($messages)->toHaveKey('city.max');
        expect($messages)->toHaveKey('host_entity.max');
        expect($messages)->toHaveKey('mobility_type.in');
        expect($messages)->toHaveKey('mobility_category.in');
        expect($messages)->toHaveKey('status.in');
        expect($messages)->toHaveKey('published_at.date');
        expect($messages)->toHaveKey('author_id.exists');
        expect($messages)->toHaveKey('reviewed_by.exists');
        expect($messages)->toHaveKey('reviewed_at.date');
        expect($messages)->toHaveKey('featured_image.image');
        expect($messages)->toHaveKey('featured_image.mimes');
        expect($messages)->toHaveKey('featured_image.max');
        expect($messages)->toHaveKey('tags.array');
        expect($messages)->toHaveKey('tags.*.required');
        expect($messages)->toHaveKey('tags.*.exists');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $messages = $request->messages();

        expect($messages['academic_year_id.required'])->toBe(__('El año académico es obligatorio.'));
        expect($messages['academic_year_id.exists'])->toBe(__('El año académico seleccionado no existe.'));
        expect($messages['program_id.exists'])->toBe(__('El programa seleccionado no existe.'));
        expect($messages['title.required'])->toBe(__('El título es obligatorio.'));
        expect($messages['title.max'])->toBe(__('El título no puede tener más de :max caracteres.'));
        expect($messages['slug.unique'])->toBe(__('Este slug ya está en uso.'));
        expect($messages['content.required'])->toBe(__('El contenido es obligatorio.'));
        expect($messages['country.max'])->toBe(__('El país no puede tener más de :max caracteres.'));
        expect($messages['city.max'])->toBe(__('La ciudad no puede tener más de :max caracteres.'));
        expect($messages['host_entity.max'])->toBe(__('La entidad de acogida no puede tener más de :max caracteres.'));
        expect($messages['mobility_type.in'])->toBe(__('El tipo de movilidad debe ser "alumnado" o "personal".'));
        expect($messages['mobility_category.in'])->toBe(__('La categoría de movilidad no es válida.'));
        expect($messages['status.in'])->toBe(__('El estado no es válido.'));
        expect($messages['published_at.date'])->toBe(__('La fecha de publicación debe ser una fecha válida.'));
        expect($messages['author_id.exists'])->toBe(__('El autor seleccionado no existe.'));
        expect($messages['reviewed_by.exists'])->toBe(__('El revisor seleccionado no existe.'));
        expect($messages['reviewed_at.date'])->toBe(__('La fecha de revisión debe ser una fecha válida.'));
        expect($messages['featured_image.image'])->toBe(__('La imagen destacada debe ser un archivo de imagen.'));
        expect($messages['featured_image.mimes'])->toBe(__('La imagen destacada debe ser de tipo: jpeg, png, jpg, webp o gif.'));
        expect($messages['featured_image.max'])->toBe(__('La imagen destacada no puede ser mayor de :max kilobytes.'));
        expect($messages['tags.array'])->toBe(__('Las etiquetas deben ser un array.'));
        expect($messages['tags.*.required'])->toBe(__('Cada etiqueta es obligatoria.'));
        expect($messages['tags.*.exists'])->toBe(__('Una o más etiquetas seleccionadas no existen.'));
    });

    it('uses custom messages in validation errors', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsPostRequest::create('/admin/noticias', 'POST', []);
        $rules = $request->rules();
        $messages = $request->messages();

        $validator = Validator::make([], $rules, $messages);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('academic_year_id'))->toBe(__('El año académico es obligatorio.'));
        expect($validator->errors()->first('title'))->toBe(__('El título es obligatorio.'));
        expect($validator->errors()->first('content'))->toBe(__('El contenido es obligatorio.'));
    });
});
