<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class AttrItem
{
    private string $key;
    private string $label;
    private string $explanation;
    private string $version;

    public function __construct(array $data, string $key, string $version)
    {
        $this->key = $key;
        $this->version = $version;
        $this->label = $data['label'];
        $this->explanation = $data['explanation'] ?? '';
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return mixed|string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return mixed|string
     */
    public function getExplanation(): string
    {
        return $this->explanation;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function __toString(): string
    {
        return $this->label;
    }
}
