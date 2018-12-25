<?php

declare(strict_types=1);

namespace App\Utils;

use InvalidArgumentException;

class ValueCorrection
{
    const USE_REGEXP = 'R';
    private $makerId;
    private $modelFieldName;
    private $wrongValue;
    private $correctedValue;
    private $options;

    public function __construct(string $makerId, string $modelFieldName, string $options, string $wrongValue, string $correctedValue)
    {
        $this->validateAndSetMakerId($makerId);
        $this->validateAndSetModelFieldName($modelFieldName);
        $this->validateAndSetOptions($options);
        $this->wrongValue = $wrongValue;
        $this->correctedValue = $correctedValue;
    }

    /**
     * @return string
     */
    public function getMakerId(): string
    {
        return $this->makerId;
    }

    /**
     * @return string
     */
    public function getModelFieldName(): string
    {
        return $this->modelFieldName;
    }

    /**
     * @return string
     */
    public function getWrongValue(): string
    {
        return $this->wrongValue;
    }

    /**
     * @return string
     */
    public function getCorrectedValue(): string
    {
        return $this->correctedValue;
    }

    public function apply($value)
    {
        if (self::USE_REGEXP === $this->options) {
            $result = preg_replace($this->wrongValue, $this->correctedValue, $value);

            if (null === $result) {
                throw new InvalidArgumentException("Regexp failed: '$this->wrongValue'");
            }

            return $result;
        } elseif ($value !== $this->wrongValue && '*' !== $this->wrongValue) {
            return $value;
        } else {
            return $this->correctedValue;
        }
    }

    /**
     * @param string $options
     */
    private function validateAndSetOptions(string $options): void
    {
        if ('' !== $options && self::USE_REGEXP !== $options) {
            throw new InvalidArgumentException("Invalid options: '$options'");
        }

        $this->options = $options;
    }

    /**
     * @param string $modelFieldName
     */
    private function validateAndSetModelFieldName(string $modelFieldName): void
    {
        if (!in_array($modelFieldName, ArtisanMetadata::getPretty2ModelFieldNameMap())) {
            throw new InvalidArgumentException("Invalid field name: '$modelFieldName'");
        }

        $this->modelFieldName = $modelFieldName;
    }

    /**
     * @param string $makerId
     */
    private function validateAndSetMakerId(string $makerId): void
    {
        if (!preg_match('#^([A-Z0-9]{7}|\*)$#', $makerId)) {
            throw new InvalidArgumentException("Invalid maker ID: '$makerId'");
        }

        $this->makerId = $makerId;
    }
}
