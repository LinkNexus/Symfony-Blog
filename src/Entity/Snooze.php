<?php

namespace App\Entity;

use App\Repository\SnoozeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SnoozeRepository::class)]
class Snooze
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'snoozedUsers')]
    private ?User $snoozedUser = null;

    #[ORM\ManyToOne(inversedBy: 'snoozes')]
    private ?User $snoozingUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSnoozedUser(): ?User
    {
        return $this->snoozedUser;
    }

    public function setSnoozedUser(?User $snoozedUser): static
    {
        $this->snoozedUser = $snoozedUser;

        return $this;
    }

    public function getSnoozingUser(): ?User
    {
        return $this->snoozingUser;
    }

    public function setSnoozingUser(?User $snoozingUser): static
    {
        $this->snoozingUser = $snoozingUser;

        return $this;
    }
}
