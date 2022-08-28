<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Ages;
use App\DataDefinitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DataInputException;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\IuSubmissions\ImportItem;
use App\Utils\StringBuffer;
use App\Utils\StrUtils;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function Psl\File\read;

class Manager
{
    final public const CMD_COMMENT = '//';
    final public const CMD_ACCEPT = 'accept';
    final public const CMD_CLEAR = 'clear';
    final public const CMD_IGNORE_UNTIL = 'ignore-until'; // Let's delay request
    final public const CMD_MATCH_TO_NAME = 'match-to-name';
    final public const CMD_REJECT = 'reject'; /* I'm sorry, but if you provided a request with zero contact info and I can't find
                                         * you using means available for a common citizen (I'm not from CIA/FBI/Facebook),
                                         * then I can't include your bare studio name on the list. No one else will be able
                                         * to find you anyway. */
    final public const CMD_REPLACE = 'replace';
    final public const CMD_SET = 'set';
    final public const CMD_WITH = 'with';

    /**
     * @var array<string, ValueCorrection[]> Associative list of corrections to be applied
     *                                       Key = submission ID or maker ID, value = correction
     */
    private array $corrections = [];

    /**
     * @var string[] List of submission IDs which got accepted (new maker or changed password)
     */
    private array $acceptedItems = [];

    /**
     * @var string[] Associative list: submission ID => matched artisan name
     */
    private array $matchedNames = [];

    /**
     * @var string[] List of submission IDs which got rejected
     */
    private array $rejectedItems = [];

    /**
     * @var DateTimeImmutable[] Associative list of requests waiting for re-validation
     *                          Key = submission ID, value = date until when ignored
     */
    private array $itemsIgnoreFinalTimes = [];

    /**
     * @var string|null Last submission ID or maker ID selected by 'WITH' command
     */
    private ?string $currentSubject = null;

    public function __construct(
        string $directives = '',
        #[Autowire('%env(resolve:CORRECTIONS_FILE_PATH)%')]
        string $directivesFilePath = '',
    ) {
        $this->readDirectives($directives);

        if ('' !== $directivesFilePath) {
            $this->readDirectives(read($directivesFilePath));
        }
    }

    public static function createFromFile(string $directivesFilePath): self
    {
        if ('' === $directivesFilePath) {
            throw new InvalidArgumentException('Corrections file path cannot be empty');
        }

        return new Manager(directivesFilePath: $directivesFilePath);
    }

    public function correctArtisan(Artisan $artisan, string $submissionId = null): void
    {
        $corrections = $this->getCorrectionsFor($artisan);

        if (null !== $submissionId) {
            $corrections = array_merge($corrections, $this->getCorrectionsFor($submissionId));
        }

        $this->applyCorrections($artisan, $corrections);
    }

    public function getMatchedName(string $submissionId): ?string
    {
        return $this->matchedNames[$submissionId] ?? null;
    }

    public function isAccepted(ImportItem $item): bool
    {
        return in_array($item->getId(), $this->acceptedItems);
    }

    public function isRejected(ImportItem $item): bool
    {
        return in_array($item->getId(), $this->rejectedItems);
    }

    public function getIgnoredUntilDate(ImportItem $item): DateTimeImmutable
    {
        return $this->itemsIgnoreFinalTimes[$item->getId()];
    }

    public function isDelayed(ImportItem $item): bool
    {
        return array_key_exists($item->getId(), $this->itemsIgnoreFinalTimes) && !UtcClock::passed($this->itemsIgnoreFinalTimes[$item->getId()]);
    }

    private function readDirectives(string $directives): void
    {
        $buffer = new StringBuffer($directives);

        $buffer->skipWhitespace();

        while (!$buffer->isEmpty()) {
            $this->readCommand($buffer);
            $buffer->skipWhitespace();
        }
    }

    private function addCorrection(string $submissionId, string $fieldName, ?string $wrongValue, string $correctedValue): void
    {
        $field = Field::from($fieldName);

        if (!array_key_exists($submissionId, $this->corrections)) {
            $this->corrections[$submissionId] = [];
        }

        $this->corrections[$submissionId][] = new ValueCorrection($submissionId, $field, $wrongValue, $correctedValue);
    }

    private function readCommand(StringBuffer $buffer): void
    {
        $command = $buffer->readUntilWhitespaceOrEof();
        $buffer->skipWhitespace();

        switch ($command) {
            case self::CMD_ACCEPT:
                $this->acceptedItems[] = $this->getCurrentSubject();
                break;

            case self::CMD_CLEAR:
                $fieldName = $buffer->readUntilWhitespaceOrEof();

                $this->addCorrection($this->getCurrentSubject(), $fieldName, null, '');
                break;

            case self::CMD_COMMENT:
                $buffer->readUntilEolOrEof();
                break;

            case self::CMD_IGNORE_UNTIL:
                $readFinalTime = $buffer->readUntilWhitespaceOrEof();

                try {
                    $parsedFinalTime = UtcClock::at($readFinalTime);
                } catch (DateTimeException $e) {
                    throw new DataInputException("Failed to parse date: '$readFinalTime'", 0, $e);
                }

                $this->itemsIgnoreFinalTimes[$this->getCurrentSubject()] = $parsedFinalTime;
                break;

            case self::CMD_MATCH_TO_NAME:
                $this->matchedNames[$this->currentSubject] = $buffer->readToken();
                break;

            case self::CMD_REJECT:
                $this->rejectedItems[] = $this->getCurrentSubject();
                break;

            case self::CMD_REPLACE:
                $fieldName = $buffer->readUntilWhitespace();
                $wrongValue = StrUtils::undoStrSafeForCli($buffer->readToken());
                $correctedValue = StrUtils::undoStrSafeForCli($buffer->readToken());

                $this->addCorrection($this->getCurrentSubject(), $fieldName, $wrongValue, $correctedValue);
                break;

            case self::CMD_SET:
                $fieldName = $buffer->readUntilWhitespace();
                $newValue = StrUtils::undoStrSafeForCli($buffer->readToken());

                $this->addCorrection($this->getCurrentSubject(), $fieldName, null, $newValue);
                break;

            case self::CMD_WITH:
                $subject = $buffer->readUntil(':');

                if (pattern('^([A-Z0-9]{7}|\d{4}-\d{2}-\d{2}_\d{6}_\d{4})$')->fails($subject)) {
                    throw new DataInputException("Invalid subject: '$subject'");
                }

                $this->currentSubject = $subject;
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
            $correctedValue = $correction->apply(StrUtils::asStr($value));

            if (Field::AGES === $correction->getField()) {
                $correctedValue = Ages::get($correctedValue);
            }

            $artisan->set($correction->getField(), $correctedValue);
        }
    }

    /**
     * @return ValueCorrection[]
     */
    private function getCorrectionsFor(Artisan|string $subject): array
    {
        if ($subject instanceof Artisan) {
            $subject = $subject->getLastMakerId();
        }

        return $this->corrections[$subject] ?? [];
    }

    private function getCurrentSubject(): string
    {
        if (null === $this->currentSubject) {
            throw new DataInputException('No current subject selected');
        }

        return $this->currentSubject;
    }
}
