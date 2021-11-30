<?php
// api/src/Entity/Exercise.php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

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
     */
    public string $name = '';

    /** The training this Exercise is matched. */
    /**
     * @ORM\ManyToOne(targetEntity="Training", inversedBy="exercises")
     */
    public ?training $training = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}