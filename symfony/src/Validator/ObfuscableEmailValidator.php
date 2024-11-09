<?php

namespace App\Validator;

use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\Enforce;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Override;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use TRegx\CleanRegex\Pattern;

class ObfuscableEmailValidator extends ConstraintValidator
{
    // Pattern taken from the Symfony's EmailValidator
    // @author Bernhard Schussek <bschussek@gmail.com>
    private const string PATTERN_HTML5_ALLOW_NO_TLD = '^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$';

    private const string ERROR_MESSAGE = 'A valid email address is required.'
        .' This field should not contain anything else, just the.email@address.'
        .' If you do not agree to provide your email, disallow any contact.';

    private readonly Pattern $emailPattern;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->emailPattern = Pattern::of(self::PATTERN_HTML5_ALLOW_NO_TLD);
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!($value instanceof Creator)) {
            throw new InvalidArgumentException(self::class.' supports only '.Creator::class.' instances');
        }

        if (ContactPermit::NO === $value->getContactAllowed()) {
            return; // No contact permit. Ignore any leftover value. Do not clear - not our responsibility.
        }

        if ($value->getEmailAddressObfuscated() === $this->getOldEmailAddressObfuscatedOrEmpty($value)) {
            if ($this->emailPattern->test($this->getOldEmailAddressOrEmpty($value))) {
                return; // The obfuscated email was not changed and the plaintext is still valid
            }
        }

        if ($this->emailPattern->test($value->getEmailAddressObfuscated())) {
            return; // New provided email address is OK
        }

        $this->context->buildViolation(self::ERROR_MESSAGE)
            ->atPath(Field::EMAIL_ADDRESS_OBFUSCATED->modelName())
            ->addViolation();
    }

    private function getOldEmailAddressObfuscatedOrEmpty(Creator $creator): string
    {
        return Enforce::string($this->entityManager->getUnitOfWork()
            ->getOriginalEntityData($creator->getArtisan())[Field::EMAIL_ADDRESS_OBFUSCATED->modelName()] ?? '');
    }

    private function getOldEmailAddressOrEmpty(Creator $creator): string
    {
        $privateDataE = $creator->getArtisan()->getPrivateData() ?? new stdClass();

        return Enforce::string($this->entityManager->getUnitOfWork()
            ->getOriginalEntityData($privateDataE)[Field::EMAIL_ADDRESS->modelName()] ?? '');
    }
}
