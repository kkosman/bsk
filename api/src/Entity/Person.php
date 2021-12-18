<?php
// api/src/Entity/Person.php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\HttpKernel\Exception\HttpException as Exception;



/** A Person. */
/**
 * @ORM\Entity
 */
#[ApiResource]
class Person
{
    /** The id of this Person. */
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /** The fullname of this Person (or null if doesn't have one). */
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Regex(
     *     pattern="/\s/",
     *     message="Your name must contain at least one space.")
     * @Assert\Regex(
     *     pattern="/\d/",
     *     match=false,
     *     message="Your name cannot contain a number.")
     */
    public ?string $fullname = null;

    /** The email of this Person. */
    /**
     * @ORM\Column
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     */
    public string $email = '';

    /** The birth date of this Person. */
    /**
     * 
     * @ORM\Column(type="datetime_immutable")
     */
    public ?\DateTimeInterface $birthday = null;

    /** @var Training[] Available trainings for this Person. */
    /**
     * @ORM\OneToMany(targetEntity="Training", mappedBy="person", cascade={"persist", "remove"})
     */
    public iterable $trainings;

    /** @var Weight[] Available weight records for this Person. */
    /**
     * @ORM\OneToMany(targetEntity="Weight", mappedBy="person", cascade={"persist", "remove"})
     */
    public iterable $weights;

    public function __construct()
    {
        $this->trainings = new ArrayCollection();
        $this->weights = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // 1. birthday - not in future, not today
        $dob = new \DateTime(null, $this->birthday->getTimezone());
        $dob->setTimestamp($this->birthday->getTimestamp());
        $now = new \DateTime(null, $this->birthday->getTimezone());

        if ($dob >= $now) {
            throw new \Exception('Birth date cannot be the current day nor a future date!');
        }
        
        // 2. birthday - min 18yo
        $difference = $now->diff($dob);
        $age = $difference->y;
        if ($age < 18) {
            throw new \Exception('User have to be at least 18 years old to use this API.');
        }

        // 3. fake and test names
        $fakeNames = ["test", "john doe", "jan kowalski"];
        if (in_array($this->fullname, $fakeNames)) {
            throw new \Exception('This name sounds fake!');
        }

        // 4. birthday - not over 120 years old
        $difference = $now->diff($dob);
        $age = $difference->y;
        if ($age > 120) {
            throw new \Exception('Person cannot be older than 120 years.');
        }
    }
}