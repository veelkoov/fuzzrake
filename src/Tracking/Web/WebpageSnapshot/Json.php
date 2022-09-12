<?php

declare(strict_types=1);

namespace App\Tracking\Web\WebpageSnapshot;

use DateTimeInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Json
{
    private static ?Serializer $serializer = null;

    private static function serializer(): Serializer
    {
        return self::$serializer ??= new Serializer([new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => DateTimeInterface::ATOM]), new ObjectNormalizer(null, null, null, new ReflectionExtractor())], [new JsonEncoder()]);
    }

    public static function serialize(Metadata $metadata): string
    {
        return self::serializer()->serialize($metadata, 'json');
    }

    public static function deserialize(string $json): Metadata
    {
        return self::serializer()->deserialize($json, Metadata::class, 'json');
    }
}
