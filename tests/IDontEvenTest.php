<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class IDontEvenTest extends TestCase
{
    /**
     * @throws TransportExceptionInterface
     */
    public function testConnectivityToRecaptcha(): void
    {
        $result = HttpClient::create()->request('HEAD', 'https://www.google.com/recaptcha/api/siteverify');
        self::assertEquals(Response::HTTP_OK, $result->getStatusCode(), 'Head lost while trying to use the internet connection');
    }
}
