<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\DataDefinitions\Ages;
use App\DataDefinitions\Fields\Field;
use App\IuHandling\Exception\ManagerConfigError;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\ValueCorrection;
use App\Utils\StringBuffer;
use App\Utils\StrUtils;

class Manager
{
    final public const CMD_COMMENT = '//';
    final public const CMD_ACCEPT = 'accept';
    final public const CMD_CLEAR = 'clear';
    final public const CMD_MATCH_TO_NAME = 'match-to-name';
    final public const CMD_REPLACE = 'replace';
    final public const CMD_SET = 'set';

    /**
     * @var ValueCorrection[]
     */
    private array $corrections = [];
    private bool $isAccepted = false;
    private ?string $matchedName = null;

    /**
     * @throws ManagerConfigError
     */
    public function __construct(
        string $directives,
    ) {
        $this->readDirectives($directives);
    }

    public function correctArtisan(Artisan $artisan): void
    {
        $corrections = $this->getCorrectionsFor();

        $this->applyCorrections($artisan, $corrections);
    }

    public function getMatchedName(): ?string
    {
        return $this->matchedName;
    }

    public function isAccepted(): bool
    {
        return $this->isAccepted;
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

    private function addCorrection(string $fieldName, ?string $wrongValue, string $correctedValue): void
    {
        $this->corrections[] = new ValueCorrection(Field::from($fieldName), $wrongValue, $correctedValue);
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
                $this->isAccepted = true;
                break;

            case self::CMD_CLEAR:
                $fieldName = $buffer->readUntilWhitespaceOrEof();

                $this->addCorrection($fieldName, null, '');
                break;

            case self::CMD_COMMENT:
                $buffer->readUntilEolOrEof();
                break;

            case self::CMD_MATCH_TO_NAME:
                $this->matchedName = $buffer->readToken();
                break;

            case self::CMD_REPLACE:
                $fieldName = $buffer->readUntilWhitespace();
                $wrongValue = StrUtils::undoStrSafeForCli($buffer->readToken());
                $correctedValue = StrUtils::undoStrSafeForCli($buffer->readToken());

                $this->addCorrection($fieldName, $wrongValue, $correctedValue);
                break;

            case self::CMD_SET:
                $fieldName = $buffer->readUntilWhitespace();
                $newValue = StrUtils::undoStrSafeForCli($buffer->readToken());

                $this->addCorrection($fieldName, null, $newValue);
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
    private function getCorrectionsFor(): array
    {
        return $this->corrections;
    }
}
