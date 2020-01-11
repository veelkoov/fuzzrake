<?php

declare(strict_types=1);

namespace App\Utils\GoogleForms;

class Item
{
    private const SHORT_TEXT = 0;
    private const LONG_TEXT = 1;
    private const CHECKBOXES = 4;
    private const SECTION = 6;
    private const DATE = 9;

    private int $index;
    private int $id;
    private string $name;
    private string $description;
    private int $type;

    /**
     * @var Answer[]
     */
    private array $answers = [];

    public function __construct(array $data, int $index)
    {
        $this->index = $index;
        $this->id = $data[0];
        $this->name = $data[1];
        $this->description = $data[2];
        $this->type = $data[3];

        foreach ($data[4] ?? [] as $answerData) {
            $this->answers[] = new Answer($answerData, $this);
        }
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return Answer[]
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function getOnlyAnswer(): Answer
    {
        if (1 !== count($this->answers)) {
            throw new GoogleFormsRuntimeException('This item does not have exactly one answer');
        }

        return $this->answers[0];
    }

    public function isFillable(): bool
    {
        return self::SECTION !== $this->type;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
