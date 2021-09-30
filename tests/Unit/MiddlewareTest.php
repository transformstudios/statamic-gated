<?php

namespace TransformStudios\Gated\Tests\Unit;

use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use TransformStudios\Gated\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    /** @test */
    public function it_adds_roles_to_query_string()
    {
        Role::make('role-one')->title('Role One')->save();
        Role::make('role-two')->title('Role Two')->save();

        $user = User::make()
            ->email('erin@transformstudios.com')
            ->set('roles', ['role-one', 'role-two'])
            ->save();

        $query = Arr::query(['roles' => ['role-one', 'role-two']]);

        $this->actingAs($user)
            ->get('/dummy-test-route')
            ->assertRedirect('http://gated.test/dummy-test-route?'.$query);
    }

    /** @test */
    public function it_doesnt_redirect_when_not_logged_in()
    {
        $response = $this->get('/dummy-test-route');

        $this->assertFalse($response->isRedirect());
    }

    /** @test */
    public function it_adds_roles_to_existing_query_string()
    {
        Role::make('role-one')->title('Role One')->save();
        Role::make('role-two')->title('Role Two')->save();

        $user = User::make()
            ->email('erin@transformstudios.com')
            ->set('roles', ['role-one', 'role-two'])
            ->save();

        $query = Arr::query(['roles' => ['role-one', 'role-two']]);

        $this->actingAs($user)
            ->get('/dummy-test-route?foo=bar')
            ->assertRedirect('http://gated.test/dummy-test-route?foo=bar&'.$query);

        $this->actingAs($user)
            ->get('/dummy-test-route?roles[]=one&roles[]=two&foo=bar')
            ->assertRedirect('http://gated.test/dummy-test-route?foo=bar&'.$query);
    }

    /** @test */
    public function it_doesnt_add_roles_to_query_string_when_user_has_no_roles()
    {
        $user = User::make()
            ->email('erin@transformstudios.com')
            ->save();

        $response = $this->actingAs($user)
            ->get('/dummy-test-route?foo=bar');

        $this->assertFalse($response->isRedirect());
    }

    /** @test */
    public function it_removes_roles_from_query_string_when_not_logged_in()
    {
        $this->get('/dummy-test-route?roles[]=foo&roles[]=bar&unrelated=baz')
            ->assertRedirect('http://gated.test/dummy-test-route?unrelated=baz');
    }

    /** @test */
    public function it_doesnt_add_roles_to_query_string_when_disabled()
    {
        Role::make('role-one')->title('Role One')->save();
        Role::make('role-two')->title('Role Two')->save();

        $user = User::make()
            ->email('erin@transformstudios.com')
            ->set('roles', ['role-one', 'role-two'])
            ->save();

        config(['gated.enabled' => false]);

        $response = $this->actingAs($user)
            ->get('/dummy-test-route?foo=bar');

        $this->assertFalse($response->isRedirect());
    }

    /** @test */
    public function it_doesnt_redirect_when_roles_already_match()
    {
        Role::make('role-one')->title('Role One')->save();
        Role::make('role-two')->title('Role Two')->save();

        $user = User::make()
            ->email('erin@transformstudios.com')
            ->set('roles', ['role-one', 'role-two'])
            ->save();

        config(['gated.enabled' => true]);

        $response = $this->actingAs($user)
            ->get('/dummy-test-route?roles[]=role-one&roles[]=role-two');

        $this->assertFalse($response->isRedirect());
    }
}
