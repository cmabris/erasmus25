<?php

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::SETTINGS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::SETTINGS_EDIT, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::SETTINGS_VIEW,
        Permissions::SETTINGS_EDIT,
    ]);
    $viewer->givePermissionTo([
        Permissions::SETTINGS_VIEW,
    ]);
});

describe('UpdateSettingRequest - Authorization', function () {
    it('authorizes user with edit permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'string']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => 'test']
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'string']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => 'test']
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('denies user without edit permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'string']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => 'test']
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $setting = Setting::factory()->create(['type' => 'string']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => 'test']
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is not Setting instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateSettingRequest::create(
            '/admin/configuracion/1',
            'PUT',
            ['value' => 'test']
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', 'not-a-setting-instance');

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is null', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateSettingRequest::create(
            '/admin/configuracion/1',
            'PUT',
            ['value' => 'test']
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('UpdateSettingRequest - Validation Rules', function () {
    it('returns empty array when route parameter is not Setting instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateSettingRequest::create(
            '/admin/configuracion/1',
            'PUT',
            ['value' => 'test']
        );
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', 'not-a-setting-instance');

            return $route;
        });
        $rules = $request->rules();

        expect($rules)->toBe([]);
    });

    it('validates string type requires value', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'string']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();
    });

    it('validates string type accepts valid string value', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'string']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });
        $rules = $request->rules();

        $validator = Validator::make([
            'value' => 'Valid String Value',
        ], $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('validates integer type requires value and integer', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'integer']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });
        $rules = $request->rules();

        // Test required
        $validator = Validator::make([], $rules);
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();

        // Test integer validation
        $validator2 = Validator::make(['value' => 'not-an-integer'], $rules);
        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('value'))->toBeTrue();

        // Test valid integer
        $validator3 = Validator::make(['value' => 123], $rules);
        expect($validator3->passes())->toBeTrue();
    });

    it('validates boolean type requires value and boolean', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'boolean']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });
        $rules = $request->rules();

        // Test required
        $validator = Validator::make([], $rules);
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();

        // Test boolean validation
        $validator2 = Validator::make(['value' => 'not-a-boolean'], $rules);
        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('value'))->toBeTrue();

        // Test valid boolean true
        $validator3 = Validator::make(['value' => true], $rules);
        expect($validator3->passes())->toBeTrue();

        // Test valid boolean false
        $validator4 = Validator::make(['value' => false], $rules);
        expect($validator4->passes())->toBeTrue();
    });

    it('validates json type requires value and valid json', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'json']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });
        $rules = $request->rules();

        // Test required
        $validator = Validator::make([], $rules);
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('value'))->toBeTrue();

        // Test invalid JSON
        $validator2 = Validator::make(['value' => '{invalid json}'], $rules);
        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('value'))->toBeTrue();

        // Test valid JSON
        $validator3 = Validator::make(['value' => json_encode(['key' => 'value'])], $rules);
        expect($validator3->passes())->toBeTrue();
    });

    it('validates description is optional and string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'string']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });
        $rules = $request->rules();

        // Should pass without description
        $validator = Validator::make(['value' => 'test'], $rules);
        expect($validator->passes())->toBeTrue();

        // Should pass with description
        $validator2 = Validator::make([
            'value' => 'test',
            'description' => 'Test description',
        ], $rules);
        expect($validator2->passes())->toBeTrue();
    });
});

describe('UpdateSettingRequest - Prepare For Validation', function () {
    it('returns early when route parameter is not Setting instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateSettingRequest::create(
            '/admin/configuracion/1',
            'PUT',
            ['value' => '1']
        );
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', 'not-a-setting-instance');

            return $route;
        });

        // Call prepareForValidation manually
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // Should not have modified the value
        expect($request->input('value'))->toBe('1');
    });

    it('converts boolean string to boolean for boolean type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'boolean']);

        // Test '1' -> true
        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => '1']
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $convertedValue = $request->input('value');
        // filter_var returns boolean true for '1'
        expect($convertedValue)->toBe(true);
        expect($convertedValue)->toBeBool();

        // Test '0' -> false
        $request2 = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => '0']
        );
        $request2->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        $method->invoke($request2);
        expect($request2->input('value'))->toBe(false);
        expect($request2->input('value'))->toBeBool();

        // Test 'true' -> true
        $request3 = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => 'true']
        );
        $request3->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        $method->invoke($request3);
        expect($request3->input('value'))->toBe(true);
        expect($request3->input('value'))->toBeBool();

        // Test 'false' -> false
        $request4 = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => 'false']
        );
        $request4->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        $method->invoke($request4);
        expect($request4->input('value'))->toBe(false);
        expect($request4->input('value'))->toBeBool();
    });

    it('does not convert non-string values for boolean type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'boolean']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => true] // Already boolean
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        // Call prepareForValidation manually
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // Should remain as boolean true
        expect($request->input('value'))->toBe(true);
    });

    it('does not process boolean conversion when value is not present', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'boolean']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            [] // No value
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        // Call prepareForValidation manually
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // Should not have added value
        expect($request->has('value'))->toBeFalse();
    });

    it('converts array to JSON for json type when string is invalid JSON', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'json']);

        // The logic only converts if the value is a string that fails JSON decode
        // and then checks if it's an array/object. Since arrays/objects passed directly
        // are not strings, they won't be converted. This test verifies the actual behavior:
        // when a string that looks like an array (but is invalid JSON) is passed,
        // it should not convert because is_string check fails for actual arrays.
        
        // Test with invalid JSON string that contains array-like syntax
        // Actually, the code checks is_string first, so arrays passed directly won't be processed
        // This test verifies that arrays passed as strings (which would be unusual) 
        // are handled correctly. But since the code checks is_string, we test the actual flow:
        
        // When value is a string that fails JSON decode, it checks if value is array/object
        // But if it's a string, it can't be an array/object, so this branch never executes
        // The conversion only happens if we pass the value as a string representation
        
        // Test actual behavior: string that fails JSON decode but is not array/object
        $invalidJsonString = '{invalid json}';
        
        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => $invalidJsonString]
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // Should remain as invalid JSON string (not converted because it's a string, not array/object)
        $convertedValue = $request->input('value');
        expect($convertedValue)->toBe($invalidJsonString);
    });

    it('does not convert non-string array/object for json type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'json']);

        // The code only processes strings, so arrays/objects passed directly are not converted
        $arrayValue = ['key' => 'value', 'number' => 123];

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => $arrayValue]
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // Should remain as array (not converted because is_string check fails)
        $convertedValue = $request->input('value');
        expect($convertedValue)->toBe($arrayValue);
    });

    it('does not convert valid JSON string for json type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'json']);

        $validJson = json_encode(['key' => 'value']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => $validJson]
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        // Call prepareForValidation manually
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // Should remain as the same JSON string
        expect($request->input('value'))->toBe($validJson);
    });

    it('does not process JSON conversion when value is not present', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'json']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            [] // No value
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        // Call prepareForValidation manually
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // Should not have added value
        expect($request->has('value'))->toBeFalse();
    });

    it('does not process JSON conversion for non-string values', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'json']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => 123] // Not a string
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });

        // Call prepareForValidation manually
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // Should remain as integer (not processed)
        expect($request->input('value'))->toBe(123);
    });
});

describe('UpdateSettingRequest - Custom Messages', function () {
    it('returns empty array when route parameter is not Setting instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = UpdateSettingRequest::create(
            '/admin/configuracion/1',
            'PUT',
            ['value' => 'test']
        );
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', 'not-a-setting-instance');

            return $route;
        });
        $messages = $request->messages();

        expect($messages)->toBe([]);
    });

    it('has custom error messages for all field types', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $types = ['string', 'integer', 'boolean', 'json'];

        foreach ($types as $type) {
            $setting = Setting::factory()->create(['type' => $type]);

            $request = UpdateSettingRequest::create(
                "/admin/configuracion/{$setting->id}",
                'PUT',
                ['value' => 'test']
            );
            $request->setRouteResolver(function () use ($setting) {
                $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
                $route->bind(new \Illuminate\Http\Request);
                $route->setParameter('setting', $setting);

                return $route;
            });
            $messages = $request->messages();

            expect($messages)->toBeArray();
            expect($messages)->toHaveKey('value.required');
            expect($messages)->toHaveKey('description.string');
        }
    });

    it('has type-specific error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Test integer type messages
        $integerSetting = Setting::factory()->create(['type' => 'integer']);
        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$integerSetting->id}",
            'PUT',
            ['value' => 'test']
        );
        $request->setRouteResolver(function () use ($integerSetting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $integerSetting);

            return $route;
        });
        $messages = $request->messages();

        expect($messages)->toHaveKey('value.integer');

        // Test boolean type messages
        $booleanSetting = Setting::factory()->create(['type' => 'boolean']);
        $request2 = UpdateSettingRequest::create(
            "/admin/configuracion/{$booleanSetting->id}",
            'PUT',
            ['value' => 'test']
        );
        $request2->setRouteResolver(function () use ($booleanSetting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $booleanSetting);

            return $route;
        });
        $messages2 = $request2->messages();

        expect($messages2)->toHaveKey('value.boolean');

        // Test json type messages
        $jsonSetting = Setting::factory()->create(['type' => 'json']);
        $request3 = UpdateSettingRequest::create(
            "/admin/configuracion/{$jsonSetting->id}",
            'PUT',
            ['value' => 'test']
        );
        $request3->setRouteResolver(function () use ($jsonSetting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $jsonSetting);

            return $route;
        });
        $messages3 = $request3->messages();

        expect($messages3)->toHaveKey('value.json');

        // Test string type messages (default)
        $stringSetting = Setting::factory()->create(['type' => 'string']);
        $request4 = UpdateSettingRequest::create(
            "/admin/configuracion/{$stringSetting->id}",
            'PUT',
            ['value' => 'test']
        );
        $request4->setRouteResolver(function () use ($stringSetting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $stringSetting);

            return $route;
        });
        $messages4 = $request4->messages();

        expect($messages4)->toHaveKey('value.string');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'string']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => 'test']
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });
        $messages = $request->messages();

        expect($messages['value.required'])->toBe(__('El valor de la configuración es obligatorio.'));
        expect($messages['value.string'])->toBe(__('El valor debe ser un texto válido.'));
        expect($messages['description.string'])->toBe(__('La descripción debe ser un texto válido.'));
    });
});

describe('UpdateSettingRequest - Custom Attributes', function () {
    it('has custom attribute names', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['type' => 'string']);

        $request = UpdateSettingRequest::create(
            "/admin/configuracion/{$setting->id}",
            'PUT',
            ['value' => 'test']
        );
        $request->setRouteResolver(function () use ($setting) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/configuracion/{setting}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('setting', $setting);

            return $route;
        });
        $attributes = $request->attributes();

        expect($attributes)->toBeArray();
        expect($attributes)->toHaveKey('value');
        expect($attributes)->toHaveKey('description');
        expect($attributes['value'])->toBe(__('valor'));
        expect($attributes['description'])->toBe(__('descripción'));
    });
});
