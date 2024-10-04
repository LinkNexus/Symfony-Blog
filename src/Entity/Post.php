<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'symfony-blog-post')]
class Post implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $audienceType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, PostModification>
     */
    #[ORM\OneToMany(targetEntity: PostModification::class, mappedBy: 'post')]
    private Collection $postModifications;

    /**
     * @var Collection<int, PostReaction>
     */
    #[ORM\OneToMany(targetEntity: PostReaction::class, mappedBy: 'post')]
    private Collection $postReactions;

    #[ORM\OneToOne(mappedBy: 'post', cascade: ['persist', 'remove'])]
    private ?PostAudience $postAudience = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'posts')]
    private Collection $categories;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, HiddenPost>
     */
    #[ORM\OneToMany(targetEntity: HiddenPost::class, mappedBy: 'post')]
    private Collection $hiddenPosts;

    public function __construct()
    {
        $this->postModifications = new ArrayCollection();
        $this->postReactions = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->comments = new ArrayCollection();
        $this->hiddenPosts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getAudienceType(): ?string
    {
        return $this->audienceType;
    }

    public function setAudienceType(string $audienceType): static
    {
        $this->audienceType = $audienceType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, PostModification>
     */
    public function getPostModifications(): Collection
    {
        return $this->postModifications;
    }

    public function addPostModification(PostModification $postModification): static
    {
        if (!$this->postModifications->contains($postModification)) {
            $this->postModifications->add($postModification);
            $postModification->setPost($this);
        }

        return $this;
    }

    public function removePostModification(PostModification $postModification): static
    {
        if ($this->postModifications->removeElement($postModification)) {
            // set the owning side to null (unless already changed)
            if ($postModification->getPost() === $this) {
                $postModification->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostReaction>
     */
    public function getPostReactions(): Collection
    {
        return $this->postReactions;
    }

    public function addPostReaction(PostReaction $postReaction): static
    {
        if (!$this->postReactions->contains($postReaction)) {
            $this->postReactions->add($postReaction);
            $postReaction->setPost($this);
        }

        return $this;
    }

    public function removePostReaction(PostReaction $postReaction): static
    {
        if ($this->postReactions->removeElement($postReaction)) {
            // set the owning side to null (unless already changed)
            if ($postReaction->getPost() === $this) {
                $postReaction->setPost(null);
            }
        }

        return $this;
    }

    public function getPostAudience(): ?PostAudience
    {
        return $this->postAudience;
    }

    public function setPostAudience(PostAudience $postAudience): static
    {
        // set the owning side of the relation if necessary
        if ($postAudience->getPost() !== $this) {
            $postAudience->setPost($this);
        }

        $this->postAudience = $postAudience;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array(
            "id" => $this->getId()
        );
    }

    /**
     * @return Collection<int, HiddenPost>
     */
    public function getHiddenPosts(): Collection
    {
        return $this->hiddenPosts;
    }

    public function addHiddenPost(HiddenPost $hiddenPost): static
    {
        if (!$this->hiddenPosts->contains($hiddenPost)) {
            $this->hiddenPosts->add($hiddenPost);
            $hiddenPost->setPost($this);
        }

        return $this;
    }

    public function removeHiddenPost(HiddenPost $hiddenPost): static
    {
        if ($this->hiddenPosts->removeElement($hiddenPost)) {
            // set the owning side to null (unless already changed)
            if ($hiddenPost->getPost() === $this) {
                $hiddenPost->setPost(null);
            }
        }

        return $this;
    }
}
