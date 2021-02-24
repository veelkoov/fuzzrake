<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\DataInputException;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\IuSubmissions\ImportItem;
use App\Utils\StringBuffer;
use App\Utils\StrUtils;
use DateTimeInterface;
use InvalidArgumentException;

class Manager
{
    public const CMD_COMMENT = '//';
    public const CMD_ACCEPT = 'accept';
    public const CMD_CLEAR = 'clear';
    public const CMD_IGNORE_PASSCODE = 'ignore-passcode';
    public const CMD_IGNORE_UNTIL = 'ignore-until'; // Let's delay request
    public const CMD_REJECT = 'reject'; /* I'm sorry, but if you provided a request with zero contact info and I can't find
                                         * you using means available for a common citizen (I'm not from CIA/FBI/Facebook),
                                         * then I can't include your bare studio name on the list. No one else will be able
                                         * to find you anyway. */
    public const CMD_REPLACE = 'replace';
    public const CMD_SET = 'set';
    public const CMD_WITH = 'with';

    /**
     * @var ValueCorrection[][] Associative list of corrections to be applied
     *                          Key = submission ID or maker ID, value = correction
     */
    private array $corrections = [];

    /**
     * @var string[] List of submission IDs which got accepted (new maker or changed password)
     */
    private array $acceptedItems = [];

    /**
     * @var string[] List of submission IDs which contain invalid passcodes, to be approved & imported
     */
    private array $itemsWithPasscodeExceptions = [];

    /**
     * @var string[] List of submission IDs which got rejected
     */
    private array $rejectedItems = [];

    /**
     * @var DateTimeInterface[] Associative list of requests waiting for re-validation
     *                          Key = submission ID, value = date until when ignored
     */
    private array $itemsIgnoreFinalTimes = [];

    /**
     * @var string|null Last submission ID or maker ID selected by 'WITH' command
     */
    private ?string $currentSubject = null;

    /**
     * @throws DataInputException
     */
    public function __construct(string $directives)
    {
        $this->readDirectives($directives);
    }

    /**
     * @throws DataInputException
     */
    public static function createFromFile(string $correctionsFilePath): self
    {
        if (!file_exists($correctionsFilePath)) {
            throw new InvalidArgumentException("File '$correctionsFilePath' does not exist");
        }

        return new Manager(file_get_contents($correctionsFilePath));
    }

    public function correctArtisan(Artisan $artisan, string $submissionId = null): void
    {
        $corrections = $this->getCorrectionsFor($artisan);

        if (null !== $submissionId) {
            $corrections = array_merge($corrections, $this->getCorrectionsFor($submissionId));
        }

        $this->applyCorrections($artisan, $corrections);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function getMatchedName(string $makerId): ?string
    {
        return null; // TODO: Implement if needed ever again GREP-CODE-CMD-MATCH-NAME
    }

    public function isAccepted(ImportItem $item): bool
    {
        return in_array($item->getId(), $this->acceptedItems);
    }

    public function isRejected(ImportItem $item): bool
    {
        return in_array($item->getId(), $this->rejectedItems);
    }

    public function isPasscodeIgnored(ImportItem $item): bool
    {
        return in_array($item->getId(), $this->itemsWithPasscodeExceptions);
    }

    public function getIgnoredUntilDate(ImportItem $item): DateTimeInterface
    {
        return $this->itemsIgnoreFinalTimes[$item->getId()];
    }

    public function isDelayed(ImportItem $item): bool
    {
        return array_key_exists($item->getId(), $this->itemsIgnoreFinalTimes) && !DateTimeUtils::passed($this->itemsIgnoreFinalTimes[$item->getId()]);
    }

    /**
     * @throws DataInputException
     */
    private function readDirectives(string $directives)
    {
        $buffer = new StringBuffer($directives);

        $buffer->skipWhitespace();

        while (!$buffer->isEmpty()) {
            $this->readCommand($buffer);
            $buffer->skipWhitespace();
        }
    }

    private function addCorrection(string $submissionId, Field $field, ?string $wrongValue, string $correctedValue): void
    {
        if (!array_key_exists($submissionId, $this->corrections)) {
            $this->corrections[$submissionId] = [];
        }

        $this->corrections[$submissionId][] = new ValueCorrection($submissionId, $field, $wrongValue, $correctedValue);
    }

    /**
     * @throws DataInputException
     */
    private function readCommand(StringBuffer $buffer): void
    {
        $command = $buffer->readUntilWhitespace();
        $buffer->skipWhitespace();

        switch ($command) {
            case self::CMD_WITH:
                $subject = $buffer->readUntil(':');

                if (pattern('^([A-Z0-9]{7}|\d{4}-\d{2}-\d{2}_\d{6}_\d{4})$')->fails($subject)) {
                    throw new DataInputException("Invalid subject: '$subject'");
                }

                $this->currentSubject = $subject;
                break;

            case self::CMD_COMMENT:
                $buffer->readUntilEolOrEof();
                break;

            case self::CMD_ACCEPT:
                $this->acceptedItems[] = $this->getCurrentSubject();
                break;

            case self::CMD_IGNORE_PASSCODE:
                $this->itemsWithPasscodeExceptions[] = $this->getCurrentSubject();
                break;

            case self::CMD_REJECT:
                $this->rejectedItems[] = $this->getCurrentSubject();
                break;

            case self::CMD_IGNORE_UNTIL:
                $readFinalTime = $buffer->readUntilWhitespace();

                try {
                    $parsedFinalTime = DateTimeUtils::getUtcAt($readFinalTime);
                } catch (DateTimeException $e) {
                    throw new DataInputException("Failed to parse date: '$readFinalTime'", 0, $e);
                }

                $this->itemsIgnoreFinalTimes[$this->getCurrentSubject()] = $parsedFinalTime;
                break;

            case self::CMD_SET:
                $fieldName = $buffer->readUntilWhitespace();
                $newValue = StrUtils::undoStrSafeForCli($buffer->readToken());

                $this->addCorrection($this->getCurrentSubject(), Fields::get($fieldName), null, $newValue);
            break;

            case self::CMD_REPLACE:
                $fieldName = $buffer->readUntilWhitespace();
                $wrongValue = StrUtils::undoStrSafeForCli($buffer->readToken());
                $correctedValue = StrUtils::undoStrSafeForCli($buffer->readToken());

                $this->addCorrection($this->getCurrentSubject(), Fields::get($fieldName), $wrongValue, $correctedValue);
            break;

            case self::CMD_CLEAR:
                $fieldName = $buffer->readUntilWhitespace();

                $this->addCorrection($this->getCurrentSubject(), Fields::get($fieldName), null, '');
            break;

            default:
                throw new DataInputException("Unknown command: '$command'");
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

    private function getCorrectionsFor($subject): array
    {
        if ($subject instanceof Artisan) {
            $subject = $subject->getLastMakerId();
        }

        return $this->corrections[$subject] ?? [];
    }

    /**
     * @throws DataInputException
     */
    private function getCurrentSubject(): string
    {
        if (null === $this->currentSubject) {
            throw new DataInputException('No current subject selected');
        }

        return $this->currentSubject;
    }
}
