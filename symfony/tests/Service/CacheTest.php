<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Data\Definitions\Fields\Field;
use App\Service\Cache;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

/**
 * @small
 */
class CacheTest extends TestCase
{
    private TagAwareAdapter $tagAwareAdapter;
    private Cache $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->tagAwareAdapter = new TagAwareAdapter(new ArrayAdapter());
        $this->subject = new Cache($this->tagAwareAdapter);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetCached(): void
    {
        /* @phpstan-ignore staticMethod.alreadyNarrowedType (Testing the contract) */
        self::assertSame('a-result-1', $this->subject->getCached('a-key-1', 'a-tag-1', fn () => 'a-result-1'));
        /* @phpstan-ignore staticMethod.impossibleType (Testing the contract) */
        self::assertSame('a-result-1', $this->subject->getCached('a-key-1', 'a-tag-1', fn () => 'a-result-2'));
        /* @phpstan-ignore staticMethod.alreadyNarrowedType (Testing the contract) */
        self::assertSame('a-result-3', $this->subject->getCached('a-key-2', 'a-tag-2', fn () => 'a-result-3'));
        /* @phpstan-ignore staticMethod.impossibleType (Testing the contract) */
        self::assertSame('a-result-3', $this->subject->getCached('a-key-2', 'a-tag-2', fn () => 'a-result-4'));

        $this->tagAwareAdapter->invalidateTags(['a-tag-1']);

        /* @phpstan-ignore staticMethod.alreadyNarrowedType (Testing the contract) */
        self::assertSame('a-result-5', $this->subject->getCached('a-key-1', 'a-tag-1', fn () => 'a-result-5'));
        /* @phpstan-ignore staticMethod.impossibleType (Testing the contract) */
        self::assertSame('a-result-3', $this->subject->getCached('a-key-2', 'a-tag-2', fn () => 'a-result-6'));
    }

    public function testKeysHandling(): void
    {
        $this->subject->get(fn () => 'a-result-1', [], ['abc', Field::FEATURES]);
        $this->subject->get(fn () => 'a-result-2', [], Field::FEATURES);

        /* @phpstan-ignore staticMethod.impossibleType (Testing the contract) */
        self::assertSame('a-result-1', $this->subject->get(fn () => 'wrong', [], ['abc', 'FEATURES']));
        /* @phpstan-ignore staticMethod.impossibleType (Testing the contract) */
        self::assertSame('a-result-2', $this->subject->get(fn () => 'wrong', [], 'FEATURES'));
        /* @phpstan-ignore staticMethod.impossibleType (Testing the contract) */
        self::assertSame('a-result-2', $this->subject->get(fn () => 'wrong', [], ['FEATURES']));
    }

    public function testKeysEscaping(): void
    {
        $this->subject->get(fn () => 'a-result-1', [], ['abc', 'def']);

        /* @phpstan-ignore staticMethod.alreadyNarrowedType (Testing the contract) */
        self::assertSame('other-1', $this->subject->get(fn () => 'other-1', [], 'abc.def'));
    }
}
