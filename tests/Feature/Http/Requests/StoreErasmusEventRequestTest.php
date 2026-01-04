<?php

use App\Http\Requests\StoreErasmusEventRequest;
use App\Models\AcademicYear;
use App\Models\Call;
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
    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::EVENTS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::EVENTS_VIEW,
        Permissions::EVENTS_CREATE,
        Permissions::EVENTS_EDIT,
        Permissions::EVENTS_DELETE,
    ]);
});

describe('StoreErasmusEventRequest - Authorization', function () {
    // Note: Authorization is tested in Livewire component tests
    // These tests focus on validation rules only
});

describe('StoreErasmusEventRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
        expect($validator->errors()->has('event_type'))->toBeTrue();
        expect($validator->errors()->has('start_date'))->toBeTrue();
    });

    it('validates title is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', [
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
        ]);
        $rules = $request->rules();

        $validator = Validator::make([
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    });

    it('validates title max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'title' => str_repeat('a', 256),
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    });

    it('validates event_type is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'title' => 'Test Event',
            'start_date' => now()->format('Y-m-d H:i:s'),
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('event_type'))->toBeTrue();
    });

    it('validates event_type is valid enum', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'title' => 'Test Event',
            'event_type' => 'invalid_type',
            'start_date' => now()->format('Y-m-d H:i:s'),
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('event_type'))->toBeTrue();
    });

    it('validates start_date is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'title' => 'Test Event',
            'event_type' => 'apertura',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('start_date'))->toBeTrue();
    });

    it('validates end_date is after start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'title' => 'Test Event',
            'event_type' => 'apertura',
            'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_date' => now()->format('Y-m-d H:i:s'),
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('end_date'))->toBeTrue();
    });

    it('validates program_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'title' => 'Test Event',
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'program_id' => 99999,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('program_id'))->toBeTrue();
    });

    it('validates call_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'title' => 'Test Event',
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'call_id' => 99999,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('call_id'))->toBeTrue();
    });

    it('validates call_id belongs to program_id', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program1->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', [
            'title' => 'Test Event',
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'program_id' => $program2->id,
            'call_id' => $call->id,
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('call_id'))->toBeTrue();
    });

    it('allows call_id when it belongs to program_id', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', [
            'title' => 'Test Event',
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        expect($validator->fails())->toBeFalse();
    });

    it('validates image is image file', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'title' => 'Test Event',
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'image' => 'not-an-image',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('image'))->toBeTrue();
    });

    it('validates image mime types', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $validator = Validator::make([
            'title' => 'Test Event',
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'image' => $file,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('image'))->toBeTrue();
    });

    it('validates image max size', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $file = UploadedFile::fake()->image('large.jpg', 800, 600)->size(6000); // 6MB

        $validator = Validator::make([
            'title' => 'Test Event',
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'image' => $file,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('image'))->toBeTrue();
    });

    it('allows valid image', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreErasmusEventRequest::create('/admin/events', 'POST', []);
        $rules = $request->rules();

        $file = UploadedFile::fake()->image('event.jpg', 800, 600);

        $validator = Validator::make([
            'title' => 'Test Event',
            'event_type' => 'apertura',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'image' => $file,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('StoreErasmusEventRequest - Custom Messages', function () {
    it('returns custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = new StoreErasmusEventRequest;
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('title.required');
        expect($messages)->toHaveKey('title.max');
        expect($messages)->toHaveKey('event_type.required');
        expect($messages)->toHaveKey('event_type.in');
        expect($messages)->toHaveKey('start_date.required');
        expect($messages)->toHaveKey('end_date.after');
        expect($messages)->toHaveKey('image.image');
        expect($messages)->toHaveKey('image.mimes');
        expect($messages)->toHaveKey('image.max');
    });
});
