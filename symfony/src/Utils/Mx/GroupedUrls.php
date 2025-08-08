<?php

declare(strict_types=1);

namespace App\Utils\Mx;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Enforce;
use Veelkoov\Debris\Base\DList;
use Veelkoov\Debris\StringList;

/**
 * @extends DList<GroupedUrl>
 */
final class GroupedUrls extends DList
{
    public static function from(Creator $creator): self
    {
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

    public function onlyWithIds(StringList $urlIds): self
    {
        return $this->filter(static fn (GroupedUrl $url) => $urlIds->contains($url->getId()));
    }

    public function onlyWithoutIds(StringList $urlIds): self
    {
        return $this->filterNot(static fn (GroupedUrl $url) => $urlIds->contains($url->getId()));
    }

    /**
     * @return string|list<string>
     */
    public function getStringOrStrList(Field $urlType): string|array
    {
        $urls = $this->filter(static fn (GroupedUrl $url) => $url->type === $urlType)
            ->mapInto(static fn (GroupedUrl $url): string => $url->url, new StringList());

        if ($urlType->isList()) {
            return $urls->getValuesArray();
        } else {
            return $urls->isEmpty() ? '' : $urls->single();
        }
    }
}
