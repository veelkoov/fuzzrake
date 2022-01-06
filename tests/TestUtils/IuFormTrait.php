<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait IuFormTrait
{
    private static function skipRulesAndCaptcha(KernelBrowser $client): void
    {
        $client->submit($client->getCrawler()->selectButton('Agree and continue')->form());
        $client->followRedirect();
    }

    private static function skipData(KernelBrowser $client, bool $fillMandatoryData): void
    {
        $data = !$fillMandatoryData ? [] : [
                'iu_form[name]'            => 'Test name',
                'iu_form[country]'         => 'Test country',
                'iu_form[ages]'            => 'ADULTS',
                'iu_form[worksWithMinors]' => 'NO',
                'iu_form[makerId]'         => 'TESTMID',
            ];

        $form = $client->getCrawler()->selectButton('Continue')->form($data);

        self::submitValid($client, $form);
    }
}
