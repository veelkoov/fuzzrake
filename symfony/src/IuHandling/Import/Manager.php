<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Fields\Field;
use App\IuHandling\Exception\ManagerConfigError;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringBuffer;
use App\Utils\StrUtils;

class Manager
{
    final public const CMD_ACCEPT = 'accept';
    final public const CMD_CLEAR = 'clear';
    final public const CMD_COMMENT = '//';
    final public const CMD_MATCH_MAKER_ID = 'match-maker-id';
    final public const CMD_SET = 'set';

    /**
     * @var ValueCorrection[]
     */
    private array $corrections = [];
    private bool $isAccepted = false;
    private ?string $matchedMakerId = null;

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
        foreach ($this->corrections as $correction) {
            $artisan->set($correction->field, $correction->value);
        }
    }

    public function getMatchedMakerId(): ?string
    {
        return $this->matchedMakerId;
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

    /**
     * @throws ManagerConfigError
     */
    private function addCorrection(string $fieldName, string $correctedValue): void
    {
        $field = Field::tryFrom($fieldName);

        if (null === $field) {
            throw new ManagerConfigError("Unknown field: '$fieldName'");
        }

        $this->corrections[] = new ValueCorrection($field, $correctedValue);
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

                $this->addCorrection($fieldName, '');
                break;

            case self::CMD_COMMENT:
                $buffer->readUntilEolOrEof();
                break;

            case self::CMD_MATCH_MAKER_ID:
                $this->matchedMakerId = $buffer->readUntilWhitespaceOrEof();
                break;

            case self::CMD_SET:
                $fieldName = $buffer->readUntilWhitespace();
                $newValue = StrUtils::undoStrSafeForCli($buffer->readToken());

                $this->addCorrection($fieldName, $newValue);
                break;

            default:
                throw new ManagerConfigError("Unknown command: '$command'");
        }
    }
}
