<?php
// api/src/Entity/Weight.php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\HttpKernel\Exception\HttpException as Exception;
use App\Validator as AppAssert;

/** A Weight of a training. */
/**
 * @ORM\Entity
 * @AppAssert\SelfRepair
 */
#[ApiResource]
class Weight
{
    /** The id of this Weight. */
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /** The weight value of this record (in kg). */
    /**
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThanOrEqual(1)
     */
    public int $weight = 0;

    /** The date of this weight record. */
    /**
     * 
     * @ORM\Column(type="datetime_immutable")
     */
    public ?\DateTimeInterface $date = null;

    /** The Person this weight record is matched to. */
    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="weights")
     */
    public ?person $person = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // 1. weight record date - not in future
        $record_date = new \DateTime(null, $this->date->getTimezone());
        $record_date->setTimestamp($this->date->getTimestamp());
        $now = new \DateTime(null, $this->date->getTimezone());

        if ($record_date > $now) {
            throw new \Exception('Weight record date cannot be a future date!');
        }
        
        // 2. weight record date - not over a year old
        $difference = $now->diff($record_date);
        $age = $difference->y;
        if ($age > 0) {
            throw new \Exception('Weight record date is too far in history!');
        }
    }

}