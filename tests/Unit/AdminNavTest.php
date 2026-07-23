<?php

namespace Tests\Unit;

use App\Support\AdminNav;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminNavTest extends TestCase
{
    public function test_is_active_matches_route_patterns(): void
    {
        $request = Request::create('/admin/crm/leads/1/edit', 'GET');
        $request->setRouteResolver(function () use ($request) {
            $route = new \Illuminate\Routing\Route(['GET'], '/admin/crm/leads/{lead}/edit', fn () => null);
            $route->name('admin.crm.leads.edit');
            $route->bind($request);

            return $route;
        });

        $this->app->instance('request', $request);

        $this->assertTrue(AdminNav::isActive('admin.crm.leads.*'));
        $this->assertTrue(AdminNav::isActive(['admin.crm.customers.*', 'admin.crm.leads.*']));
        $this->assertFalse(AdminNav::isActive('admin.crm.customers.*'));
        $this->assertSame('active-page', AdminNav::linkClass('admin.crm.leads.*'));
        $this->assertStringContainsString('dropdown-open', AdminNav::dropdownClass('admin.crm.leads.*'));
        $this->assertSame('true', AdminNav::expanded('admin.crm.leads.*'));
    }
}
