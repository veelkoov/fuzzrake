<?php

declare(strict_types=1);

namespace App\Utils\GoogleForms;

class Option
{
    /**
     * @var string
     */
    private $name;

    public function __construct(array $data)
    {
        $this->name = $data[0];
        // 1 - nullable
        // 2 - nullable
        // 3 - nullable
        // 4 - int
        // ...?
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
