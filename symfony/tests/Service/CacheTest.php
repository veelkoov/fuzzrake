<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Data\Definitions\Fields\Field;
use App\Service\Cache;
use Override;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

#[Small]
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
    public function testGet(): void
    {
        /* @phpstan-ignore staticMethod.alreadyNarrowedType (Testing the contract) */
        self::assertSame('a-result-1', $this->subject->get(static fn () => 'a-result-1', 'a-tag-1', 'a-key-1'));
        /* @phpstan-ignore staticMethod.impossibleType (Testing the contract) */
        self::assertSame('a-result-1', $this->subject->get(static fn () => 'a-result-2', 'a-tag-1', 'a-key-1'));
        /* @phpstan-ignore staticMethod.alreadyNarrowedType (Testing the contract) */
        self::assertSame('a-result-3', $this->subject->get(static fn () => 'a-result-3', 'a-tag-2', 'a-key-2'));
        /* @phpstan-ignore staticMethod.impossibleType (Testing the contract) */
        self::assertSame('a-result-3', $this->subject->get(static fn () => 'a-result-4', 'a-tag-2', 'a-key-2'));

        $this->tagAwareAdapter->invalidateTags(['a-tag-1']);

        /* @phpstan-ignore staticMethod.alreadyNarrowedType (Testing the contract) */
        self::assertSame('a-result-5', $this->subject->get(static fn () => 'a-result-5', 'a-tag-1', 'a-key-1'));
        /* @phpstan-ignore staticMethod.impossibleType (Testing the contract) */
        self::assertSame('a-result-3', $this->subject->get(static fn () => 'a-result-6', 'a-tag-2', 'a-key-2'));
    }

    public function testKeysHandling(): void
    {
        self::assertSame(
            $this->subject->getKeyFromParts(Field::FEATURES),
            $this->subject->getKeyFromParts('FEATURES'),
        );

        self::assertSame(
            $this->subject->getKeyFromParts(Field::FEATURES),
            $this->subject->getKeyFromParts(['FEATURES']),
        );

        self::assertSame(
            $this->subject->getKeyFromParts(['abc', Field::FEATURES]),
            $this->subject->getKeyFromParts(['abc', 'FEATURES']),
        );

        self::assertNotSame(
            $this->subject->getKeyFromParts(['abc', 'FEATURES']),
            $this->subject->getKeyFromParts('abc.FEATURES'),
        );
    }
}
