<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;

class ImportCorrector
{
    private $corrections = ['*' => []];
    private $acknowledgedNew = [];
    private $matchedNames = [];

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
            $this->readCommand($buffer);
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

    private function readCommand(StringBuffer $buffer): void
    {
        $command = $buffer->readUntil(':');
        $makerId = $buffer->readUntil(':');

        switch ($command) {
            case 'ack new':
                $this->acknowledgedNew[] = $makerId;
            break;

            case 'match name':
                $this->matchedNames[$makerId] = $buffer->readUntil(':');
            break;

            default:
                $matchedName = $buffer->readUntil(':');
                $delimiter = $buffer->readUntil(':');
                $wrongValue = Utils::unsafeStr($buffer->readUntil($delimiter));
                $correctedValue = Utils::unsafeStr($buffer->readUntil($delimiter));

                $this->addCorrection(new ValueCorrection($makerId, ArtisanMetadata::PRETTY_TO_MODEL_FIELD_NAMES_MAP[$matchedName],
                    $command, $wrongValue, $correctedValue));
            break;
        }
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

    public function getMatchedName($makerId): ?string
    {
        if (!array_key_exists($makerId, $this->matchedNames)) {
            return null;
        } else {
            return $this->matchedNames[$makerId];
        }
    }

    public function isAcknowledged($makerId): bool
    {
        return in_array($makerId, $this->acknowledgedNew);
    }
}
