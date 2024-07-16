<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Cache;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

/**
 * @small
 */
class CacheTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testGetCached(): void
    {
        $tagAwareAdapter = new TagAwareAdapter(new ArrayAdapter());
        $subject = new Cache($tagAwareAdapter);

        self::assertEquals('a-result-1', $subject->getCached('a-key-1', 'a-tag-1', fn () => 'a-result-1'));
        self::assertEquals('a-result-1', $subject->getCached('a-key-1', 'a-tag-1', fn () => 'a-result-2'));
        self::assertEquals('a-result-3', $subject->getCached('a-key-2', 'a-tag-2', fn () => 'a-result-3'));
        self::assertEquals('a-result-3', $subject->getCached('a-key-2', 'a-tag-2', fn () => 'a-result-4'));

        $tagAwareAdapter->invalidateTags(['a-tag-1']);

        self::assertEquals('a-result-5', $subject->getCached('a-key-1', 'a-tag-1', fn () => 'a-result-5'));
        self::assertEquals('a-result-3', $subject->getCached('a-key-2', 'a-tag-2', fn () => 'a-result-6'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGet(): void
    {
        // TODO: dots and key creation
    }
}
