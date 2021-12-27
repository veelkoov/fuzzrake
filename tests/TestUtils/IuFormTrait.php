<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait IuFormTrait {
    private static function skipRulesAndCaptcha(KernelBrowser $client): void
    {
        $client->submit($client->getCrawler()->selectButton('Agree and continue')->form());
    }
}
