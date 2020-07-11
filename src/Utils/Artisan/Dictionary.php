<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

abstract class Dictionary
{
    protected array $keyValueMap;
    protected array $keyKeyMap;

    public function __construct(array $attributes)
    {
        $this->keyValueMap = [];

        foreach ($attributes[$this->getAttributeKey()]['values'] as $version => $items) {
            $this->keyValueMap += $this->getVersionAttrItems($version, $items);
        }
    }

    abstract public function getAttributeKey(): string;

    /**
     * @return AttrItem[]
     */
    public function getValues(): array
    {
        return $this->keyValueMap;
    }

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        return array_keys($this->keyValueMap);
    }

    /**
     * @return string[]
     */
    public function getKeyKeyMap(): array
    {
        return $this->keyKeyMap ?? $this->keyKeyMap = array_combine($this->getKeys(), $this->getKeys());
    }

    public function getValuesAsString(): string
    {
        return implode("\n", $this->keyValueMap);
    }

    public function count(): int
    {
        return count($this->keyValueMap);
    }

    /**
     * @return AttrItem[]
     */
    private function getVersionAttrItems(string $version, array $items): array
    {
        $result = [];

        foreach ($items as $key => $data) {
            $result[$key] = new AttrItem($data, $key, $version);
        }

        return $result;
    }
}
