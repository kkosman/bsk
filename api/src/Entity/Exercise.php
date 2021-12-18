<?php
// api/src/Entity/Exercise.php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\HttpKernel\Exception\HttpException as Exception;

/** A Exercise of a training. */
/**
 * @ORM\Entity
 */
#[ApiResource]
class Exercise
{
    /** The id of this Exercise. */
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /** The name of the Exercise. */
    /**
     * @ORM\Column
     * @Assert\Length(
     *      min = 3,
     *      max = 10,
     *      minMessage = "Exercise name must be at least {{ limit }} characters long.",
     *      maxMessage = "Exercise name cannot be longer than {{ limit }} characters."
     * )
     */
    public string $name = '';

    /** The calories burned with this exercise per minute (in kcal). */
    /**
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThanOrEqual(1)
     */
    public int $calories = 0;

    /** The training this Exercise is matched. */
    /**
     * @ORM\ManyToOne(targetEntity="Training", inversedBy="exercises")
     */
    public ?training $training = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        // 1. calories cannot be greater nor equal to 1000 per minute

        if ($this->calories >= 1000) {
            throw new \Exception('No exercies is able to burn more than 1000 calories per minute!');
        }
    }

}