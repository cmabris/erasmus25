<?php

use App\Models\Program;

use function Tests\Browser\Helpers\createPublicTestData;

it('can visit the home page', function () {
    Program::factory()->count(3)->create(['is_active' => true]);

    $page = visit('/');

    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('displays active programs on home page', function () {
    $program = Program::factory()->create([
        'name' => 'Test Active Program',
        'is_active' => true,
    ]);

    $page = visit('/');

    $page->assertSee('Test Active Program')
        ->assertNoJavascriptErrors();
});

it('displays public content using helper', function () {
    $data = createPublicTestData();

    $page = visit('/');

    $page->assertSee($data['program']->name)
        ->assertSee($data['call']->title)
        ->assertSee($data['news']->title)
        ->assertNoJavascriptErrors();
});
