<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Utils;

use App\Data\Definitions\Fields\Field;
use App\Utils\CacheKey;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\CacheItem;

#[Small]
class CacheKeyTest extends TestCase
{
    public function testGet(): void
    {
        // Expected
        $separator = '.';
        $wrapper = '_';

        // Test handling Field enum
        self::assertSame(CacheKey::get(Field::FEATURES), CacheKey::get('FEATURES'));

        // Test handling array
        self::assertSame(CacheKey::get(Field::FEATURES), CacheKey::get(['FEATURES']));

        // Test handling array and Field enum
        self::assertSame(CacheKey::get(['abc', Field::FEATURES]), CacheKey::get(['abc', 'FEATURES']));

        // Test separator
        self::assertSame("abc{$separator}def", CacheKey::get(['abc', 'def']));

        // Test wrapping special characters
        self::assertSame("abc{$wrapper}64{$wrapper}def", CacheKey::get('abc@def'));
        self::assertSame("abc{$wrapper}95{$wrapper}def", CacheKey::get("abc{$wrapper}def"));
        self::assertSame("abc{$wrapper}46{$wrapper}def", CacheKey::get("abc{$separator}def"));

        // Test cache key of a cache key is something different
        self::assertNotSame(
            CacheKey::get(['abc', 'def']),
            CacheKey::get(CacheKey::get(['abc', 'def'])),
        );
    }

    public function testInvalidCharactersHandling(): void
    {
        self::expectNotToPerformAssertions();

        $invalidKey = '`~[]{}\\|;:\'",<.>/?!@#$%^&*()+=-_.';

        try {
            CacheItem::validateKey($invalidKey);
            self::fail('Should not have been valid.');
        } catch (InvalidArgumentException) {
            // Expected
        }

        CacheItem::validateKey(CacheKey::get($invalidKey));
    }
}
