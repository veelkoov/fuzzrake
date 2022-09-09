<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\DataDefinitions\Ages;
use App\DataDefinitions\Fields\Field;
use App\IuHandling\Exception\ManagerConfigError;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\ValueCorrection;
use App\Utils\DataInputException;
use App\Utils\StringBuffer;
use App\Utils\StrUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function Psl\File\read;
use function Psl\Filesystem\exists;

class Manager
{
    final public const CMD_COMMENT = '//';
    final public const CMD_ACCEPT = 'accept';
    final public const CMD_CLEAR = 'clear';
    final public const CMD_MATCH_TO_NAME = 'match-to-name';
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
     * @var string|null Last submission ID or maker ID selected by 'WITH' command
     */
    private ?string $currentSubject = null;

    /**
     * @throws ManagerConfigError
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        string $directives = '',
        #[Autowire('%env(resolve:CORRECTIONS_FILE_PATH)%')]
        string $directivesFilePath = '', // TODO: Remove
    ) {
        $this->readDirectives($directives);

        if ('' !== $directivesFilePath) {
            if (exists($directivesFilePath)) {
                $this->readDirectives(read($directivesFilePath));
            } else {
                $this->logger->warning("Configured directives file does not exist: '$directivesFilePath'");
            }
        }
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

    public function isAccepted(SubmissionData $item): bool
    {
        return in_array($item->getId(), $this->acceptedItems);
    }

    /**
     * @throws ManagerConfigError
     */
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

    /**
     * @throws ManagerConfigError
     */
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

            case self::CMD_MATCH_TO_NAME:
                $this->matchedNames[$this->currentSubject] = $buffer->readToken();
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
                    throw new ManagerConfigError("Invalid subject: '$subject'");
                }

                $this->currentSubject = $subject;
                break;

            default:
                throw new ManagerConfigError("Unknown command: '$command'");
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
