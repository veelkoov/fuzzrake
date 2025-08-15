<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Data\Definitions\ContactPermit;
use App\Entity\Creator;
use App\Entity\CreatorId;
use App\Entity\CreatorOfferStatus;
use App\Entity\CreatorPrivateData;
use App\Entity\CreatorSpecie;
use App\Entity\CreatorUrl;
use App\Entity\CreatorValue;
use App\Entity\CreatorVolatileData;
use App\Entity\Specie;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

#[Small]
class CreatorSmallTest extends TestCase
{
    /**
     * @var list<string>
     */
    private static array $ignoredTypes = [
        'int', // Handles ?int $id
        DateTimeImmutable::class,
        Specie::class,
    ];

    /**
     * @throws ReflectionException
     */
    public function testDeepCloningIsComplete(): void
    {
        $subject = new Creator()
            ->setContactAllowed(ContactPermit::CORRECTIONS)
            ->setHasAllergyWarning(true)
            ->setOffersPaymentPlans(false)
        ;
        $subject->addCreatorId(new CreatorId());
        $subject->setPrivateData(new CreatorPrivateData());
        $subject->setVolatileData(new CreatorVolatileData());
        $subject->addOfferStatus(new CreatorOfferStatus());
        $subject->addValue(new CreatorValue());

        $specie = new Specie();
        $subject->addSpecie(new CreatorSpecie()->setSpecie($specie));

        $creatorUrl = new CreatorUrl();
        $creatorUrl->getState();
        $subject->addUrl($creatorUrl);

        $result = clone $subject;
        $this->assureDifferent('', [], $subject, $result);
    }

    /**
     * @param list<mixed> $subjectParents
     *
     * @throws ReflectionException
     */
    private function assureDifferent(string $path, array $subjectParents, mixed $subject, mixed $result): void
    {
        $subjectParentsAndThis = [...$subjectParents, $subject];
        if (is_scalar($subject)) {
            return;
        }

        if (is_array($subject)) {
            self::assertIsArray($result);
            self::assertEquals(array_keys($subject), array_keys($result));

            self::assertNotEmpty($subject, "$path is an empty array, cannot test cloning.");

            foreach (array_keys($subject) as $key) {
                $this->assureDifferent("{$path}[$key]", $subjectParentsAndThis, $subject[$key], $result[$key]);
            }

            return;
        }

        self::assertNotNull($subject, "$path is null, cannot test cloning.");
        self::assertIsObject($subject, "$path is not an object.");
        self::assertIsObject($result, "$path is not an object.");

        $reflection = new ReflectionClass($subject);
        if ($reflection->isEnum()) {
            return;
        }

        self::assertNotSame($subject, $result, "$path (".get_debug_type($subject).') is identical.');

        foreach ($reflection->getProperties() as $property) {
            $propertyPath = "$path.$property->name";

            self::assertTrue($property->hasType(), "$propertyPath is missing type.");
            $type = $property->getType();
            self::assertInstanceOf(ReflectionNamedType::class, $type, "$propertyPath type ($type) is not a named type.");

            if (arr_contains(self::$ignoredTypes, $type->getName())) {
                continue;
            }

            $subjectValue = $property->getValue($subject);
            if (arr_contains($subjectParents, $subjectValue)) {
                continue;
            }

            $this->assureDifferent($propertyPath, $subjectParentsAndThis, $subjectValue, $property->getValue($result));
        }
    }
}
