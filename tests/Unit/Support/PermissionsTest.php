<?php

use App\Support\Permissions;

it('defines all program permission constants correctly', function () {
    expect(Permissions::PROGRAMS_VIEW)->toBe('programs.view')
        ->and(Permissions::PROGRAMS_CREATE)->toBe('programs.create')
        ->and(Permissions::PROGRAMS_EDIT)->toBe('programs.edit')
        ->and(Permissions::PROGRAMS_DELETE)->toBe('programs.delete')
        ->and(Permissions::PROGRAMS_ALL)->toBe('programs.*');
});

it('defines all call permission constants correctly', function () {
    expect(Permissions::CALLS_VIEW)->toBe('calls.view')
        ->and(Permissions::CALLS_CREATE)->toBe('calls.create')
        ->and(Permissions::CALLS_EDIT)->toBe('calls.edit')
        ->and(Permissions::CALLS_DELETE)->toBe('calls.delete')
        ->and(Permissions::CALLS_PUBLISH)->toBe('calls.publish')
        ->and(Permissions::CALLS_ALL)->toBe('calls.*');
});

it('defines all news permission constants correctly', function () {
    expect(Permissions::NEWS_VIEW)->toBe('news.view')
        ->and(Permissions::NEWS_CREATE)->toBe('news.create')
        ->and(Permissions::NEWS_EDIT)->toBe('news.edit')
        ->and(Permissions::NEWS_DELETE)->toBe('news.delete')
        ->and(Permissions::NEWS_PUBLISH)->toBe('news.publish')
        ->and(Permissions::NEWS_ALL)->toBe('news.*');
});

it('defines all document permission constants correctly', function () {
    expect(Permissions::DOCUMENTS_VIEW)->toBe('documents.view')
        ->and(Permissions::DOCUMENTS_CREATE)->toBe('documents.create')
        ->and(Permissions::DOCUMENTS_EDIT)->toBe('documents.edit')
        ->and(Permissions::DOCUMENTS_DELETE)->toBe('documents.delete')
        ->and(Permissions::DOCUMENTS_ALL)->toBe('documents.*');
});

it('defines all event permission constants correctly', function () {
    expect(Permissions::EVENTS_VIEW)->toBe('events.view')
        ->and(Permissions::EVENTS_CREATE)->toBe('events.create')
        ->and(Permissions::EVENTS_EDIT)->toBe('events.edit')
        ->and(Permissions::EVENTS_DELETE)->toBe('events.delete')
        ->and(Permissions::EVENTS_ALL)->toBe('events.*');
});

it('defines all user permission constants correctly', function () {
    expect(Permissions::USERS_VIEW)->toBe('users.view')
        ->and(Permissions::USERS_CREATE)->toBe('users.create')
        ->and(Permissions::USERS_EDIT)->toBe('users.edit')
        ->and(Permissions::USERS_DELETE)->toBe('users.delete')
        ->and(Permissions::USERS_ALL)->toBe('users.*');
});

it('returns all permissions when calling all()', function () {
    $permissions = Permissions::all();

    expect($permissions)->toBeArray()
        ->toHaveCount(32)
        ->toContain(Permissions::PROGRAMS_VIEW)
        ->toContain(Permissions::CALLS_PUBLISH)
        ->toContain(Permissions::NEWS_PUBLISH)
        ->toContain(Permissions::DOCUMENTS_VIEW)
        ->toContain(Permissions::EVENTS_VIEW)
        ->toContain(Permissions::USERS_VIEW);
});

it('returns permissions grouped by module when calling byModule()', function () {
    $permissionsByModule = Permissions::byModule();

    expect($permissionsByModule)->toBeArray()
        ->toHaveKeys(['programs', 'calls', 'news', 'documents', 'events', 'users'])
        ->and($permissionsByModule['programs'])->toBeArray()->toHaveCount(5)
        ->and($permissionsByModule['calls'])->toBeArray()->toHaveCount(6)
        ->and($permissionsByModule['news'])->toBeArray()->toHaveCount(6)
        ->and($permissionsByModule['documents'])->toBeArray()->toHaveCount(5)
        ->and($permissionsByModule['events'])->toBeArray()->toHaveCount(5)
        ->and($permissionsByModule['users'])->toBeArray()->toHaveCount(5);
});

it('includes correct permissions for programs module', function () {
    $programPermissions = Permissions::byModule()['programs'];

    expect($programPermissions)->toContain(Permissions::PROGRAMS_VIEW)
        ->toContain(Permissions::PROGRAMS_CREATE)
        ->toContain(Permissions::PROGRAMS_EDIT)
        ->toContain(Permissions::PROGRAMS_DELETE)
        ->toContain(Permissions::PROGRAMS_ALL);
});

it('includes correct permissions for calls module', function () {
    $callPermissions = Permissions::byModule()['calls'];

    expect($callPermissions)->toContain(Permissions::CALLS_VIEW)
        ->toContain(Permissions::CALLS_CREATE)
        ->toContain(Permissions::CALLS_EDIT)
        ->toContain(Permissions::CALLS_DELETE)
        ->toContain(Permissions::CALLS_PUBLISH)
        ->toContain(Permissions::CALLS_ALL);
});

it('returns view-only permissions when calling viewOnly()', function () {
    $viewPermissions = Permissions::viewOnly();

    expect($viewPermissions)->toBeArray()
        ->toHaveCount(5)
        ->toContain(Permissions::PROGRAMS_VIEW)
        ->toContain(Permissions::CALLS_VIEW)
        ->toContain(Permissions::NEWS_VIEW)
        ->toContain(Permissions::DOCUMENTS_VIEW)
        ->toContain(Permissions::EVENTS_VIEW)
        ->not->toContain(Permissions::PROGRAMS_CREATE)
        ->not->toContain(Permissions::CALLS_PUBLISH);
});

it('does not include user view permission in viewOnly()', function () {
    $viewPermissions = Permissions::viewOnly();

    expect($viewPermissions)->not->toContain(Permissions::USERS_VIEW);
});
