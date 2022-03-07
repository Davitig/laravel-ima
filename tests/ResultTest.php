<?php

namespace Davitig\Ima\Tests;

use Davitig\Ima\Result;
use Illuminate\Support\HtmlString;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    protected $okData = <<<EOL
RESULT: OK
TRANSACTION_ID: IDENTIFIER
SOME_PARAM1: SOME_VALUE1
EOL;

    protected $failData = <<<EOL
RESULT: FAILED
SOME_PARAM1: SOME_VALUE1
EOL;

    protected $warningData = 'warning: msg';

    protected $errorData = 'error: msg';

    protected $result;

    protected function setUp(): void
    {
        $this->result = new Result($this->okData, '127.0.0.1');
    }

    public function testGetTransactionIdFromResult(): void
    {
        $this->assertSame('IDENTIFIER', $this->result->getTransId());
    }

    public function testGetAnyParameterFromResult(): void
    {
        $this->assertSame('SOME_VALUE1', $this->result->get('some_param1'));
    }

    public function testOkResultData(): void
    {
        $this->assertTrue($this->result->success());
    }

    public function testFailedResultData(): void
    {
        $this->result->setResult($this->failData);

        $this->assertTrue($this->result->failed());
    }

    public function testWarningResultData(): void
    {
        $this->result->setResult($this->warningData);

        $this->assertTrue($this->result->isWarning());
        $this->assertSame('msg', $this->result->getWarning());
    }

    public function testErrorResultData(): void
    {
        $this->result->setResult($this->errorData);

        $this->assertTrue($this->result->isError());
        $this->assertSame('msg', $this->result->getError());
    }

    public function testRedirectToPayment(): void
    {
        $this->assertInstanceOf(HtmlString::class, $this->result->redirectToPayment());
    }
}
