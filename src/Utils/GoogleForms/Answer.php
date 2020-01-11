<?php

declare(strict_types=1);

namespace App\Utils\GoogleForms;

class Answer
{
    private int $id;
    private bool $required;
    private Item $parent;

    /**
     * @var Option[]
     */
    private array $options = [];

    public function __construct(array $data, Item $parent)
    {
        $this->id = $data[0];

        foreach ($data[1] ?? [] as $optionData) {
            $this->options[] = new Option($optionData);
        }

        $this->required = (bool) $data[2]; // int?
        // 3... - unknown

        $this->parent = $parent;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOnlyOption(): Option
    {
        if (1 !== count($this->options)) {
            throw new GoogleFormsRuntimeException('This answer for item "'.$this->parent->getName().'" does not have exactly one option, but '.count($this->options).': '.implode(', ', $this->options));
        }

        return $this->options[0];
    }

    public function isRequired(): bool
    {
        return $this->required;
    }
}
