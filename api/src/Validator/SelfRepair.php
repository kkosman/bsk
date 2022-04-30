<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SelfRepair extends Constraint
{
    public $message = 'Weight value of {{ string }} seems invalid.{{ possibilites }}';
    //public $mode = 'strict'; // If the constraint has configuration options, define them as public properties

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}