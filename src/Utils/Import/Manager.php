<?php

declare(strict_types=1);

namespace App\Utils\Import;

use App\Entity\Artisan;
use App\Utils\Artisan\Fields;
use App\Utils\DateTimeException;
use App\Utils\DateTimeUtils;
use App\Utils\StrUtils;
use DateTime;
use InvalidArgumentException;

class Manager
{
    const CMD_ACK_NEW = 'ack new';
    const CMD_REJECT = 'reject'; /* I'm sorry, but if you provided a request with zero contact info and I can't find
                                  * you using means available for a common citizen (I'm not from CIA/FBI/Facebook),
                                  * then I can't include your bare studio name on the list. No one else will be able
                                  * to find you anyway. */
    const CMD_MATCH_NAME = 'match name';
    const CMD_IGNORE_PIN = 'ignore pin';
    const CMD_SET_PIN = 'set pin';
    const CMD_IGNORE_UNTIL = 'ignore until'; // Let's temporarily ignore request
    const CMD_IGNORE_REST = 'ignore rest';

    private $corrections = ['*' => []];
    private $acknowledgedNewItems = [];
    private $matchedNames = [];

    /**
     * @var array List of hashes of rows which contain invalid passcodes, to be approved & imported
     */
    private $itemsWithPasscodeExceptions = [];

    /**
     * @var array List of hashes of rows which contain passcodes supposed to be set / to replace earlier
     */
    private $itemsWithNewPasscodes = [];

    /**
     * @var array List of hashes of rows which got rejected
     */
    private $rejectedItems = [];

    /**
     * @var DateTime[] Associative list of requests waiting for re-validation. Key = row hash, value = date until when ignored
     */
    private $itemsIgnoreFinalTimes = [];

    /**
     * @throws DateTimeException
     */
    public function __construct(string $correctionDirectivesFilePath)
    {
        $this->readDirectivesFromFile($correctionDirectivesFilePath);
    }

    /**
     * @throws ImportException
     */
    public static function createFromFile(string $correctionsFilePath): self
    {
        if (!file_exists($correctionsFilePath)) {
            throw new InvalidArgumentException("File '$correctionsFilePath' does not exist");
        }

        try {
            return new Manager($correctionsFilePath);
        } catch (DateTimeException $e) {
            throw new ImportException('Failed initializing import corrector due to incorrect date format', 0, $e);
        }
    }

    public function correctArtisan(Artisan $artisan): void
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

    public function isAcknowledged(ImportItem $item): bool
    {
        return in_array($item->getMakerId(), $this->acknowledgedNewItems);
    }

    public function shouldIgnorePasscode(ImportItem $item): bool
    {
        return in_array($item->getHash(), $this->itemsWithPasscodeExceptions);
    }

    public function isNewPasscode(ImportItem $item): bool
    {
        return in_array($item->getHash(), $this->itemsWithNewPasscodes);
    }

    public function isRejected(ImportItem $item): bool
    {
        return in_array($item->getHash(), $this->rejectedItems);
    }

    public function getIgnoredUntilDate(ImportItem $item): DateTime
    {
        return $this->itemsIgnoreFinalTimes[$item->getHash()];
    }

    public function isDelayed(ImportItem $item): bool
    {
        return array_key_exists($item->getHash(), $this->itemsIgnoreFinalTimes) && !DateTimeUtils::passed($this->itemsIgnoreFinalTimes[$item->getHash()]);
    }

    /**
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
     * @throws DateTimeException
     */
    private function readCommand(StringBuffer $buffer): void
    {
        $command = $buffer->readUntil(':');
        $makerId = $buffer->readUntil(':');

        switch ($command) {
            case self::CMD_IGNORE_REST:
                $buffer->flush();
                break;

            case self::CMD_ACK_NEW:
                $this->acknowledgedNewItems[] = $makerId;
                break;

            case self::CMD_MATCH_NAME:
                $this->matchedNames[$makerId] = $buffer->readUntil(':');
                break;

            case self::CMD_IGNORE_PIN:
                // Maker ID kept only informative
                $this->itemsWithPasscodeExceptions[] = $buffer->readUntil(':');
                break;

            case self::CMD_SET_PIN:
                // Maker ID kept only informative
                $this->itemsWithNewPasscodes[] = $buffer->readUntil(':');
                break;

            case self::CMD_REJECT:
                // Maker ID kept only informative
                $this->rejectedItems[] = $buffer->readUntil(':');
                break;

            case self::CMD_IGNORE_UNTIL:
                // Maker ID kept only informative
                $rawDataHash = $buffer->readUntil(':');
                $this->itemsIgnoreFinalTimes[$rawDataHash] = DateTimeUtils::getUtcAt($buffer->readUntil(':'));
                break;

            default:
                $fieldName = $buffer->readUntil(':');
                $delimiter = $buffer->readUntil(':');
                $wrongValue = StrUtils::undoStrSafeForCli($buffer->readUntil($delimiter));
                $correctedValue = StrUtils::undoStrSafeForCli($buffer->readUntil($delimiter));

                $this->addCorrection(new ValueCorrection($makerId, Fields::get($fieldName),
                    $command, $wrongValue, $correctedValue));
                break;
        }
    }

    private function applyCorrections(Artisan $artisan, array $corrections): void
    {
        foreach ($corrections as $correction) {
            $value = $artisan->get($correction->getField());
            $correctedValue = $correction->apply($value);
            $artisan->set($correction->getField(), $correctedValue);
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
