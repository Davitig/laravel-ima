<?php

namespace Davitig\Ima\Tests;

use Davitig\Ima\Ima;
use Davitig\Ima\ImaServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ImaServiceProviderTest extends TestCase
{
    protected $app;

    protected $provider;

    protected function setUp(): void
    {
        $this->app = new Application;

        $this->provider = new ImaServiceProvider($this->app);
    }

    public function testRegisters(): void
    {
        $this->app->offsetSet('config', new Repository(require $this->provider::configPath()));
        $this->app->offsetSet('request', new Request);

        $this->provider->register();

        $ima = $this->app->make(Ima::class);

        $this->assertInstanceOf(Ima::class, $ima);
    }

    public function testServicesProvided(): void
    {
        $this->assertContains(Ima::class, $this->provider->provides());
    }
}
