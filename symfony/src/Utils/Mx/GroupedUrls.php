<?php

declare(strict_types=1);

namespace App\Utils\Mx;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Utils\Collections\Arrays;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Enforce;

final readonly class GroupedUrls
{
    /**
     * @param list<GroupedUrl> $urls
     */
    public function __construct(
        public array $urls,
    ) {
    }

    public static function from(
        Creator $creator,
    ): self {
        $urls = [];

        foreach (Fields::urls() as $field) {
            if (!$field->providedIn($creator)) {
                continue;
            }

            $fieldValue = $creator->get($field);

            if (is_string($fieldValue)) {
                $fieldValue = [$fieldValue];
            }

            foreach (Enforce::strList($fieldValue) as $index => $url) {
                $urls[] = new GroupedUrl($field, $index, $url);
            }
        }

        return new self($urls);
    }

    /**
     * @param string[] $urlIds
     */
    public function onlyWithIds(array $urlIds): self
    {
        return new self(list_filter($this->urls, static fn (GroupedUrl $url) => arr_contains($urlIds, $url->getId())));
    }

    public function minus(self $removedUrls): self
    {
        return new self(list_filter(
            $this->urls,
            static fn (GroupedUrl $url): bool => !array_any(
                $removedUrls->urls,
                static fn (GroupedUrl $other): bool => $other->getId() === $url->getId(),
            ),
        ));
    }

    /**
     * @return string|list<string>
     */
    public function getStringOrStrList(Field $urlType): string|array
    {
        $urls = arr_map(
            list_filter($this->urls, fn (GroupedUrl $url): bool => $url->type === $urlType),
            static fn (GroupedUrl $url): string => $url->url,
        );

        if ($urlType->isList()) {
            return $urls;
        } else {
            return [] === $urls ? '' : Arrays::single($urls);
        }
    }
}
