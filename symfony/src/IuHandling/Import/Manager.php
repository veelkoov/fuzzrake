<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Fields\Field;
use App\Data\FieldValue;
use App\IuHandling\Exception\ManagerConfigError;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\StringBuffer;
use App\Utils\StrUtils;
use InvalidArgumentException;

class Manager
{
    final public const string CMD_ACCEPT = 'accept';
    final public const string CMD_CLEAR = 'clear';
    final public const string CMD_COMMENT = '//';
    final public const string CMD_MATCH_CREATOR_ID = 'match-maker-id';
    final public const string CMD_SET = 'set';

    /**
     * @var list<ValueCorrection>
     */
    private array $corrections = [];
    private bool $isAccepted = false;
    private ?string $matchedCreatorId = null;

    /**
     * @throws ManagerConfigError
     */
    public function __construct(
        string $directives,
    ) {
        $this->readDirectives($directives);
    }

    public function correctCreator(Creator $creator): void
    {
        foreach ($this->corrections as $correction) {
            $creator->set($correction->field, $correction->value);
        }
    }

    public function getMatchedCreatorId(): ?string
    {
        return $this->matchedCreatorId;
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

        try {
            $correctedValue = FieldValue::fromString($field, $correctedValue);
        } catch (InvalidArgumentException $ex) {
            throw new ManagerConfigError("Wrong value for '$fieldName': {$ex->getMessage()}", $ex->getCode(), $ex);
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

            case self::CMD_MATCH_CREATOR_ID:
                $this->matchedCreatorId = $buffer->readUntilWhitespaceOrEof();
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
