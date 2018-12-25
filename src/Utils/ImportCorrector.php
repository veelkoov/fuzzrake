<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;

class ImportCorrector
{
    private $corrections = ['*' => []];

    public function __construct(string $correctionDirectivesFilePath)
    {
        $this->readDirectivesFromFile($correctionDirectivesFilePath);
    }

    public function correctArtisan(Artisan $artisan)
    {
        $this->applyCorrections($artisan, $this->getCorrectionsFor($artisan));
    }

    private function readDirectivesFromFile(string $filePath)
    {
        $buffer = new StringBuffer(file_get_contents($filePath));

        $buffer->skipWhitespace();

        while (!$buffer->isEmpty()) {
            $this->addCorrection($this->readCorrection($buffer));

            $buffer->skipWhitespace();
        }
    }

    private function addCorrection(ValueCorrection $correction): void
    {
        $makerId = $correction->getMakerId();

        if (!array_key_exists($makerId, $this->corrections)) {
            $this->corrections[$makerId] = [];
        }

        $this->corrections[$makerId][] = $correction;
    }

    private function readCorrection(StringBuffer $buffer): ValueCorrection
    {
        $makerId = $buffer->readUntil(':');
        $fieldName = $buffer->readUntil(':');
        $delimiter = $buffer->readUntil(':');
        $options = $buffer->readUntil(':');
        $wrongValue = $buffer->readUntil($delimiter);
        $correctedValue = $buffer->readUntil($delimiter);

        return new ValueCorrection($makerId, ArtisanMetadata::PRETTY_TO_MODEL_FIELD_NAMES_MAP[$fieldName], $options, $wrongValue, $correctedValue);
    }

    /**
     * @param Artisan $artisan
     * @param $corrections ValueCorrection[]
     */
    private function applyCorrections(Artisan $artisan, array $corrections): void
    {
        foreach ($corrections as $correction) {
            $fieldName = $correction->getModelFieldName();

            $value = $artisan->get($fieldName);
            $correctedValue = $correction->apply($value);
            $artisan->set($fieldName, $correctedValue);
        }
    }

    private function getCorrectionsFor(Artisan $artisan): array
    {
        if (array_key_exists($artisan->getMakerId(), $this->corrections)) {
            return array_merge($this->corrections['*'], $this->corrections[$artisan->getMakerId()]);
        } else {
            return $this->corrections['*'];
        }
    }
}
