<?php

declare(strict_types=1);

namespace App\Utils\DataInput;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\StrUtils;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

class Manager
{
    public const CMD_ACK_NEW = 'ack new';
    public const CMD_REPLACE = 'replace';
    public const CMD_REJECT = 'reject'; /* I'm sorry, but if you provided a request with zero contact info and I can't find
                                         * you using means available for a common citizen (I'm not from CIA/FBI/Facebook),
                                         * then I can't include your bare studio name on the list. No one else will be able
                                         * to find you anyway. */
    public const CMD_MATCH_NAME = 'match name';
    public const CMD_IGNORE_PIN = 'ignore pin';
    public const CMD_SET_PIN = 'set pin';
    public const CMD_IGNORE_UNTIL = 'ignore until'; // Let's temporarily ignore request
    public const CMD_IGNORE_REST = 'ignore rest';

    private array $corrections = [];
    private array $acknowledgedNewItems = [];
    private array $matchedNames = [];

    /**
     * @var string[] List of hashes of rows which contain invalid passcodes, to be approved & imported
     */
    private array $itemsWithPasscodeExceptions = [];

    /**
     * @var string[] List of hashes of rows which contain passcodes supposed to be set as new / to replace earlier
     */
    private array $itemsWithNewPasscodes = [];

    /**
     * @var string[] List of hashes of rows which got rejected
     */
    private array $rejectedItems = [];

    /**
     * @var DateTimeInterface[] Associative list of requests waiting for re-validation. Key = row hash, value = date until when ignored
     */
    private array $itemsIgnoreFinalTimes = [];

    /**
     * @throws DataInputException
     */
    public function __construct(string $correctionDirectivesFilePath)
    {
        $this->readDirectivesFromFile($correctionDirectivesFilePath);
    }

    /**
     * @throws DataInputException
     */
    public static function createFromFile(string $correctionsFilePath): self
    {
        if (!file_exists($correctionsFilePath)) {
            throw new InvalidArgumentException("File '$correctionsFilePath' does not exist");
        }

        return new Manager($correctionsFilePath);
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
     * @throws DataInputException
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

    private function addCorrection(string $makerId, Field $field, string $wrongValue, string $correctedValue): void
    {
        if (!array_key_exists($makerId, $this->corrections)) {
            $this->corrections[$makerId] = [];
        }

        $this->corrections[$makerId][] = new ValueCorrection($makerId, $field, $wrongValue, $correctedValue);
    }

    /**
     * @throws DataInputException
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
                try {
                    $this->itemsIgnoreFinalTimes[$rawDataHash] = DateTimeUtils::getUtcAt($buffer->readUntil(':'));
                } catch (DateTimeException $e) {
                    throw new DataInputException('Failed to parse date', 0, $e);
                }
                break;

            case self::CMD_REPLACE:
                $fieldName = $buffer->readUntil(':');
                $delimiter = $buffer->readUntil(':');
                $wrongValue = StrUtils::undoStrSafeForCli($buffer->readUntil($delimiter));
                $correctedValue = StrUtils::undoStrSafeForCli($buffer->readUntil($delimiter));

                $this->addCorrection($makerId, Fields::get($fieldName), $wrongValue, $correctedValue);
            break;

            default:
                throw new DataInputException("Unknown command: {$command}");
        }
    }

    /**
     * @param ValueCorrection[] $corrections
     */
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
        return $this->corrections[$artisan->getMakerId()] ?? [];
    }
}
