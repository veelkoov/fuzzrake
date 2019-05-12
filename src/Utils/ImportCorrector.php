<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use App\Utils\ArtisanFields as Fields;

class ImportCorrector
{
    const CMD_ACK_NEW = 'ack new';
    const CMD_REJECT = 'reject'; /* I'm sorry, but if you provided a request with zero contact info and I can't find
                                  * you using means available for a common citizen (I'm not from CIA/FBI/Facebook),
                                  * then I can't include your bare studio name on the list. No one else will be able
                                  * to find you anyway. */
    const CMD_MATCH_NAME = 'match name';
    const CMD_IGNORE_PIN = 'ignore pin';

    private $corrections = ['*' => []];
    private $acknowledgedNew = [];
    private $matchedNames = [];

    /**
     * @var array List of raw data hashes which contain invalid passcodes, to be approved & imported
     */
    private $passcodeExceptions = [];

    /**
     * @var array List of raw data hashes which were rejected
     */
    private $rejectedRecords = [];

    public function __construct(string $correctionDirectivesFilePath)
    {
        $this->readDirectivesFromFile($correctionDirectivesFilePath);
    }

    public function correctArtisan(Artisan $artisan)
    {
        $this->applyCorrections($artisan, $this->getCorrectionsFor($artisan));
    }

    public function getMatchedName(string $makerId): ?string
    {
        if (!array_key_exists($makerId, $this->matchedNames)) {
            return null;
        } else {
            return $this->matchedNames[$makerId];
        }
    }

    public function isAcknowledged(string $makerId): bool
    {
        return in_array($makerId, $this->acknowledgedNew);
    }

    public function ignoreInvalidPasscodeForData(string $rawDataHash)
    {
        return in_array($rawDataHash, $this->passcodeExceptions);
    }

    public function isRejected(string $rawDataHash)
    {
        return in_array($rawDataHash, $this->rejectedRecords);
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
            case self::CMD_ACK_NEW:
                $this->acknowledgedNew[] = $makerId;
            break;

            case self::CMD_MATCH_NAME:
                $this->matchedNames[$makerId] = $buffer->readUntil(':');
            break;

            case self::CMD_IGNORE_PIN:
                // Maker ID kept only informative
                $this->passcodeExceptions[] = $buffer->readUntil(':');
            break;

            case self::CMD_REJECT:
                // Maker ID kept only informative
                $this->rejectedRecords[] = $buffer->readUntil(':');
            break;

            default:
                $fieldName = $buffer->readUntil(':');
                $delimiter = $buffer->readUntil(':');
                $wrongValue = Utils::unsafeStr($buffer->readUntil($delimiter));
                $correctedValue = Utils::unsafeStr($buffer->readUntil($delimiter));

                $this->addCorrection(new ValueCorrection($makerId, Fields::get($fieldName),
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
            $modelName = $correction->getField()->modelName();

            $value = $artisan->get($modelName);
            $correctedValue = $correction->apply($value);
            $artisan->set($modelName, $correctedValue);
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
