<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

final class RequestContext implements Context
{
    private Response $response;

    public function __construct(private KernelInterface $kernel)
    {
    }

    #[When('I request :path using HTTP method :method')]
    public function iRequestUsingHttpMethod(string $path, string $method): void
    {
        $request = Request::create($path, $method);
        $this->response = $this->kernel->handle($request);

        // if you have kernel.terminate listeners, don't forget these lines ;)
        Assert::assertInstanceOf(Kernel::class, $this->kernel);
        $this->kernel->terminate($request, $this->response);
    }

    #[Then('the status code must be :statusCode')]
    public function theStatusCodeMustBe(int $statusCode): void
    {
        $actualStatusCode = $this->response->getStatusCode();

        // the beginning of the response shows the error: in the CLI, Symfony renders the exception as a text dump
        $failureMessage = \sprintf(
            'Expected the status code %d, got %d. Response: %s',
            $statusCode,
            $actualStatusCode,
            mb_substr(trim(strip_tags((string) $this->response->getContent())), 0, 500),
        );

        // assertEquals and not assertSame: with PHPUnit 13, a failing assertSame outside the PHPUnit runner (in Behat)
        // throws a TypeError from PHPUnit\TextUI\Configuration\Registry instead of the failure message
        Assert::assertEquals($statusCode, $actualStatusCode, $failureMessage);
    }

    #[Then('the response should be :value')]
    public function theResponseShouldBe(string $value): void
    {
        $content = $this->response->getContent();
        Assert::assertIsString($content);

        // assertEquals and not assertSame: an answer returned as an integer (e.g. 42) must match "42"
        Assert::assertEquals(
            $value,
            json_decode($content),
            \sprintf('Expected the response "%s", got %s.', $value, $content),
        );
    }
}
