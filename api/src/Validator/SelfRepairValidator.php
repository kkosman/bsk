<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Entity\Weight;

class SelfRepairValidator extends ConstraintValidator
{
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function validate($object, Constraint $constraint)
    {
        function standardDeviation($array) 
        {
            $count = count($array);
            $variance = 0.0;
            $average = array_sum($array)/$count;
                
            foreach($array as $x)
                $variance += pow(($x - $average), 2);
                
            $result = (float)sqrt($variance/$count);

            return $result;
        }

        function isWeightViable($weight,$weights)
        {
            // We need at least 3 other records to compare to.
            if(count($weights) < 3) {
                // I assume that in general adults are in 50-200kg range.
                if($weight > 200 || $weight < 50)
                    return false;
            } else {
                // Check if value is outside 3 sigma range
                $average = array_sum($weights)/count($weights);
                $sigma = standardDeviation($weights);

                if($weight < floor($average-($sigma*3)) || $weight > floor($average+($sigma*3)))
                    return false;   
            }

            return true;
        }

        if (!$constraint instanceof SelfRepair) {
            throw new UnexpectedTypeException($constraint, SelfRepair::class);
        }

        /* 
         * Slef repair process
         * Step 1: self-diagnosis -> validate weight value within 3 sigma range
         */

        $try_to_correct = false;
        $weight = $object->weight;
        $possibilites_message = "";

        // Fetch all objects to have a reference. 
        // Check if object person exists in request, it is not validated before.
        if(empty($object->person))
        {
            $this->context->buildViolation($constraint->messagePersonEmpty)
                ->atPath('person')
                ->addViolation();

            return;
        }

        $query = $this->em->createQuery(
            'SELECT w
            FROM App\Entity\Weight w
            WHERE w.person = :person_id'
        )->setParameter('person_id', $object->person->getId());

        $results = $query->getResult();

        $weights = [];
        foreach ($results as $w)
            array_push($weights, $w->weight);
        
        $try_to_correct = !isWeightViable($weight,$weights);
        
        /* 
         * Step 2: self-repair 
         * -> try to fix it by moving comma
         */

        $possible_viable_values = [];
        
        if(isWeightViable($weight/10,$weights)) array_push($possible_viable_values, $weight/10);
        if(isWeightViable($weight/100,$weights)) array_push($possible_viable_values, $weight/100);
        if(isWeightViable($weight*10,$weights)) array_push($possible_viable_values, $weight*10);
        if(isWeightViable($weight*100,$weights)) array_push($possible_viable_values, $weight*100);

        if(!empty($possible_viable_values)) $possibilites_message .= " Maybe you mistyped a comma? Did you mean: ". implode(",",$possible_viable_values) . "?";

        /* 
         * Step 3: self-repair 
         * -> try to convert from pounds to kilograms
         */

        if(isWeightViable($weight*0.45359237,$weights))
            $possibilites_message .= " Maybe you measured in pounds? Did you mean: ". $weight*0.45359237 . "?";

        if ($try_to_correct) {
            if(empty($possibilites_message))
            {
                $average = array_sum($weights)/count($weights);
                $sigma = standardDeviation($weights);
                $min = floor($average-($sigma*3));
                $max = floor($average+($sigma*3));
                $possibilites_message = " Expected value is between $min and $max kilograms.";
            }
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $object->weight)
                ->setParameter('{{ possibilites }}', $possibilites_message)
                ->atPath('weight')
                ->addViolation();
        }
    }

}