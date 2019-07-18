<?php

declare(strict_types=1);

namespace App\Utils\Import;

use App\Entity\Artisan;
use App\Utils\ArtisanFields as Fields;
use App\Utils\DateTimeException;
use App\Utils\DateTimeUtils;
use App\Utils\Utils;
use DateTime;

class Corrector
{
    const CMD_ACK_NEW = 'ack new';
    const CMD_REJECT = 'reject'; /* I'm sorry, but if you provided a request with zero contact info and I can't find
                                  * you using means available for a common citizen (I'm not from CIA/FBI/Facebook),
                                  * then I can't include your bare studio name on the list. No one else will be able
                                  * to find you anyway. */
    const CMD_MATCH_NAME = 'match name';
    const CMD_IGNORE_PIN = 'ignore pin';
    const CMD_IGNORE_UNTIL = 'ignore until'; // Let's temporarily ignore request

    private $corrections = ['*' => []];
    private $acknowledgedNew = [];
    private $matchedNames = [];

    /**
     * @var array List of hashes of rows which contain invalid passcodes, to be approved & imported
     */
    private $passcodeExceptions = [];

    /**
     * @var array List of hashes of rows which got rejected
     */
    private $rejectedRecords = [];

    /**
     * @var DateTime[] Associative list of requests waiting for re-validation. Key = row hash, value = date until when ignored
     */
    private $ignoredUntil;

    /**
     * @param string $correctionDirectivesFilePath
     *
     * @throws DateTimeException
     */
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

    public function shouldIgnorePasscode(Row $row)
    {
        return in_array($row->getHash(), $this->passcodeExceptions);
    }

    public function isRejected(Row $row)
    {
        return in_array($row->getHash(), $this->rejectedRecords);
    }

    /**
     * @param string $filePath
     *
     * @throws DateTimeException
     */
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

    /**
     * @param StringBuffer $buffer
     *
     * @throws DateTimeException
     */
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

            case self::CMD_IGNORE_UNTIL:
                // Maker ID kept only informative
                $rawDataHash = $buffer->readUntil(':');
                $this->ignoredUntil[$rawDataHash] = DateTimeUtils::getUtcAt($buffer->readUntil(':'));
                break;

            default:
                $fieldName = $buffer->readUntil(':');
                $delimiter = $buffer->readUntil(':');
                $wrongValue = Utils::undoStrSafeForCli($buffer->readUntil($delimiter));
                $correctedValue = Utils::undoStrSafeForCli($buffer->readUntil($delimiter));

                $this->addCorrection(new ValueCorrection($makerId, Fields::get($fieldName),
                    $command, $wrongValue, $correctedValue));
                break;
        }
    }

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

    public function getIgnoredUntilDate(Row $row): DateTime
    {
        return $this->ignoredUntil[$row->getHash()];
    }

    /**
     * @param Row $row
     *
     * @return bool
     */
    public function isDelayed(Row $row)
    {
        return array_key_exists($row->getHash(), $this->ignoredUntil) && !DateTimeUtils::passed($this->ignoredUntil[$row->getHash()]);
    }
}
