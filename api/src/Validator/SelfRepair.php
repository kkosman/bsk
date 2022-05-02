<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SelfRepair extends Constraint
{
    public $message = 'Weight value of {{ string }} seems invalid.{{ possibilites }}';
    public $messagePersonEmpty = 'Please provide a person object.';
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}