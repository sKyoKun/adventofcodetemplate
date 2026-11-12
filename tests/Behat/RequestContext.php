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
    public function iRequestUsingHttpMethod(mixed $path, string $method): void
    {
        $request = Request::create($path, $method);
        $this->response = $this->kernel->handle($request);

        // si vous avez des evenements dans le kernel terminate, ne pas oublier ces lignes ;)
        Assert::assertInstanceOf(Kernel::class, $this->kernel);
        $this->kernel->terminate($request, $this->response);
    }

    #[Then('the status code must be :statusCode')]
    public function theStatusCodeMustBe(int $statusCode): void
    {
        $resStatusCode = $this->response->getStatusCode();

        Assert::AssertEquals($statusCode, $resStatusCode);
    }

    #[Then('the response should be :value')]
    public function theResponseShouldContainTableNode(mixed $value): void
    {
        Assert::assertIsString($this->response->getContent());
        $content = json_decode($this->response->getContent());

        Assert::assertEquals($value, $content);
    }
}
