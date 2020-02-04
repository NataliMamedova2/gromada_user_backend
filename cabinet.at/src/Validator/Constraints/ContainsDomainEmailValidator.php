<?php
declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsDomainEmailValidator extends ConstraintValidator
{

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContainsDomainEmail) {
            throw new UnexpectedTypeException($constraint, ContainsDomainEmail::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($constraint, ContainsDomainEmail::class);
        }
        $keywords = "/^[-a-z0-9!#$%&'*+?^_`{|}~]+(\.[-a-z0-9!#$%&'*+?^_`{|}~]+)*@([a-zа-яіїєґ0-9]([-a-zа-яіїєґ0-9]{0,61}[a-zа-яіїєґ0-9])?\.)+(gov\.ua|укр)$/i";
        if (!\preg_match($keywords,
            $value, $matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{string}}', $value)
                ->addViolation();
        }
    }
}
