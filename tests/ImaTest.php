<?php

namespace Davitig\Ima\Tests;

use Curl\Curl;
use Davitig\Ima\Ima;
use Davitig\Ima\ImaServiceProvider;
use Illuminate\Config\Repository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Test\TestCase;

class ImaTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $curl;

    protected $ima;

    protected function setUp(): void
    {
        $this->curl = Mockery::mock(Curl::class);

        $this->curl->shouldReceive('setUrl', 'setOpt');

        $this->ima = new Ima($this->curl, $this->getConfig(), '127.0.0.1');
    }

    public function testAmountFormat(): void
    {
        $this->assertEquals('100', $this->ima->formatAmount('1.00'));
    }

    public function testTransaction(): void
    {
        $this->curl->shouldReceive('post')->andReturn('RESULT: OK');

        $result = $this->ima->transaction([]);

        $this->assertTrue($result->success());
    }

    protected function getConfig(): Repository
    {
        return new Repository(require ImaServiceProvider::configPath());
    }
}
