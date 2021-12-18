<?php
// api/src/Entity/Training.php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\HttpKernel\Exception\HttpException as Exception;

/** A Training. */
/**
 * @ORM\Entity
 */
#[ApiResource]
class Training
{
    /** The id of this Training. */
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /** The description of this Training (or null if doesn't have one). */
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(
     *      min = 10,
     *      max = 50,
     *      minMessage = "Training description name must be at least {{ limit }} characters long.",
     *      maxMessage = "Training description cannot be longer than {{ limit }} characters."
     * )
     */
    public ?string $description = null;

    /** The type of this Training. */
    /**
     * @ORM\Column
     */
    public string $type = '';

    /** The duration of this Training (in minutes). */
    /**
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThanOrEqual(1)
     */
    public int $duration = 0;

    /** The date of this Training. */
    /**
     * 
     * @ORM\Column(type="datetime_immutable")
     */
    public ?\DateTimeInterface $date = null;

    /** @var Exercise[] Available exercises for this Training. */
    /**
     * @ORM\OneToMany(targetEntity="Exercise", mappedBy="training", cascade={"persist", "remove"})
     */
    public iterable $exercises;

    /** The Person this Trainging is matched to. */
    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="trainings")
     */
    public ?Person $person = null;

    public function __construct()
    {
        $this->exercises = new ArrayCollection();
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
        // 1. type enum validation
        $trainingTypes = ["kondycyjny", "siłowy", "obwodowy", "funkcjonalny", "relaksacyjny"];
        if (!in_array($this->type, $trainingTypes)) {
            throw new \Exception('This training type is not allowed!');
        }

        // 2. training date - not in future
        $dot = new \DateTime(null, $this->date->getTimezone());
        $dot->setTimestamp($this->date->getTimestamp());
        $now = new \DateTime(null, $this->date->getTimezone());

        if ($dot > $now) {
            throw new \Exception('Training date cannot be a future date!');
        }
        
        // 3. training date - not too old
        $difference = $now->diff($dot);
        $age = $difference->y;
        if ($age > 0) {
            throw new \Exception('Training date is too far in history!');
        }
    }
}