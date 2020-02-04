<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsDomainEmail extends Constraint
{
    public $message = 'cabinet.validator.email';
}
