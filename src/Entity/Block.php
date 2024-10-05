<?php

namespace App\Entity;

use App\Repository\BlockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
#[ORM\Table(name: 'blog_blocks')]
class Block
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'blockedUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $blockedUser = null;

    #[ORM\ManyToOne(inversedBy: 'blocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $blockingUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBlockedUser(): ?User
    {
        return $this->blockedUser;
    }

    public function setBlockedUser(?User $blockedUser): static
    {
        $this->blockedUser = $blockedUser;

        return $this;
    }

    public function getBlockingUser(): ?User
    {
        return $this->blockingUser;
    }

    public function setBlockingUser(?User $blockingUser): static
    {
        $this->blockingUser = $blockingUser;

        return $this;
    }
}
