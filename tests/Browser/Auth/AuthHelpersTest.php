<?php

use App\Support\Roles;

use function Tests\Browser\Helpers\createAuthTestUser;
use function Tests\Browser\Helpers\performLogin;

it('can log in using createAuthTestUser and performLogin helpers', function () {
    $user = createAuthTestUser(['email' => 'authhelper@test.com'], Roles::VIEWER);

    $page = performLogin($user);

    $page->assertPathIs('/dashboard')
        ->assertNoJavascriptErrors();

    $this->assertAuthenticated();
});
