<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;

class ImportCorrector
{
    private $corrections = [];

    public function __construct(string $correctionDirectivesFilePath)
    {
        $this->readDirectivesFromFile($correctionDirectivesFilePath);
    }

    public function correctArtisan(Artisan $artisan)
    {
        foreach ($this->corrections as $fieldName => $fieldCorrections) {
            $modelFieldName = ArtisanMetadata::IU_FORM_TO_MODEL_FIELDS_MAP[$fieldName];
            $value = $artisan->get($modelFieldName);

            if (array_key_exists($value, $fieldCorrections)) {
                $artisan->set($modelFieldName, $fieldCorrections[$value]);
            }
        }
    }

    private function readDirectivesFromFile(string $filePath)
    {
        $buffer = new StringBuffer(file_get_contents($filePath));

        $buffer->skipWhitespace();

        while (!$buffer->isEmpty()) {
            $this->addCorrection(...$this->readCorrection($buffer));

            $buffer->skipWhitespace();
        }
    }

    private function addCorrection(string $fieldName, string $wrongValue, string $correctedValue): void
    {
        if (!array_key_exists($fieldName, $this->corrections)) {
            $this->corrections[$fieldName] = [];
        }

        $this->corrections[$fieldName][$wrongValue] = $correctedValue;
    }

    private function readCorrection(StringBuffer $buffer): array
    {
        $fieldName = $buffer->readUntil(':');
        $delimiter = $buffer->readUntil(':');
        $wrongValue = $buffer->readUntil($delimiter);
        $correctedValue = $buffer->readUntil($delimiter);

        return [$fieldName, $wrongValue, $correctedValue];
    }
}
