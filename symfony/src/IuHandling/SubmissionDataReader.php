<?php

declare(strict_types=1);

namespace App\IuHandling;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Entity\Submission;
use App\IuHandling\Exception\SubmissionException;
use App\Utils\Enforce;
use App\Utils\FieldReadInterface;
use App\Utils\Json;
use JsonException;
use Override;

readonly class SubmissionDataReader implements FieldReadInterface
{
    /**
     * @var psJsonFieldsData
     */
    private array $parsed;

    /**
     * @throws JsonException
     */
    public function __construct(Submission $submission)
    {
        $this->parsed = SchemaFixer::fix(Json::decode($submission->getPayload())); // @phpstan-ignore argument.type (FIXME: https://github.com/veelkoov/fuzzrake/issues/293)
    }

    #[Override]
    public function get(Field $field): mixed
    {
        $fieldName = $field->value;

        if (!array_key_exists($fieldName, $this->parsed)) {
            throw new SubmissionException("Submission data is missing $fieldName");
        }

        $value = $this->parsed[$fieldName];

        if ($field->isList() && !is_array($value)) {
            throw new SubmissionException("Expected an array for $fieldName, got '$value' instead.");
        }

        if (Field::AGES === $field) {
            $value = Ages::get(Enforce::nString($value));
        }

        if (Field::CONTACT_ALLOWED === $field) {
            $value = ContactPermit::get(Enforce::nString($value));
        }

        return $value;
    }

    #[Override]
    public function getString(Field $field): string
    {
        return Enforce::string($this->get($field));
    }

    #[Override]
    public function getStringList(Field $field): array
    {
        return Enforce::strList($this->get($field));
    }

    #[Override]
    public function hasData(Field $field): bool
    {
        return $field->providedIn($this);
    }
}
