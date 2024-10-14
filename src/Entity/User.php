<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[UniqueEntity('email', message: 'There is already an account with this email')]
#[UniqueEntity('slug')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Please enter your username')]
    #[Assert\Length(
        min: 5,
        minMessage: 'The username must have at least {{ limit }} characters',
    )]
    #[Assert\Regex(pattern: '/^[a-zA-Z]/', message: 'The username must start with a letter')]
    #[Assert\Regex(pattern: '/[0-9a-zA-Z_]*/', message: 'The username must only contain letters, numbers and underscores')]
    #[Assert\Regex(pattern: '/[0-9a-zA-Z]$/', message: 'The username must end with a letter or number')]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Please enter your email')]
    #[Assert\Email(message: 'The email {{ value }} is not valid')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Please enter your gender')]
    private ?string $gender = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Please enter your birthdate')]
    #[Assert\Type('\DateTimeInterface')]
    #[Assert\LessThanOrEqual('-15 years', message: 'You must be at least 15 years old in order to access this website')]
    private ?\DateTimeImmutable $bornAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $joinedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'owner')]
    private Collection $posts;

    /**
     * @var Collection<int, PostReaction>
     */
    #[ORM\OneToMany(targetEntity: PostReaction::class, mappedBy: 'owner')]
    private Collection $postReactions;

    /**
     * @var Collection<int, PostAudience>
     */
    #[ORM\ManyToMany(targetEntity: PostAudience::class, mappedBy: 'users')]
    private Collection $postAudiences;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLoggedInAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLinkRequestedAt = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, CommentReaction>
     */
    #[ORM\OneToMany(targetEntity: CommentReaction::class, mappedBy: 'owner')]
    private Collection $commentReactions;

    /**
     * @var Collection<int, HiddenComment>
     */
    #[ORM\OneToMany(targetEntity: HiddenComment::class, mappedBy: 'user')]
    private Collection $hiddenComments;

    /**
     * @var Collection<int, Warning>
     */
    #[ORM\OneToMany(targetEntity: Warning::class, mappedBy: 'user')]
    private Collection $warnings;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $blockedTill = null;

    /**
     * @var Collection<int, Block>
     */
    #[ORM\OneToMany(targetEntity: Block::class, mappedBy: 'blockedUser')]
    private Collection $blockedUsers;

    /**
     * @var Collection<int, Block>
     */
    #[ORM\OneToMany(targetEntity: Block::class, mappedBy: 'blockingUser')]
    private Collection $blocks;

    /**
     * @var Collection<int, Snooze>
     */
    #[ORM\OneToMany(targetEntity: Snooze::class, mappedBy: 'snoozedUser')]
    private Collection $snoozedUsers;

    /**
     * @var Collection<int, Snooze>
     */
    #[ORM\OneToMany(targetEntity: Snooze::class, mappedBy: 'snoozingUser')]
    private Collection $snoozes;

    /**
     * @var Collection<int, HiddenPost>
     */
    #[ORM\OneToMany(targetEntity: HiddenPost::class, mappedBy: 'user')]
    private Collection $hiddenPosts;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'users', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->joinedAt = new \DateTimeImmutable();
        $this->lastLoggedInAt = new \DateTimeImmutable();
        $this->posts = new ArrayCollection();
        $this->postReactions = new ArrayCollection();
        $this->postAudiences = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->commentReactions = new ArrayCollection();
        $this->hiddenComments = new ArrayCollection();
        $this->warnings = new ArrayCollection();
        $this->blockedUsers = new ArrayCollection();
        $this->blocks = new ArrayCollection();
        $this->snoozedUsers = new ArrayCollection();
        $this->snoozes = new ArrayCollection();
        $this->hiddenPosts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        if ($this->username === 'Nexus Administration') {
            $roles[] = 'ROLE_ADMIN';
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBornAt(): ?\DateTimeImmutable
    {
        return $this->bornAt;
    }

    public function setBornAt(\DateTimeImmutable $bornAt): static
    {
        $this->bornAt = $bornAt;

        return $this;
    }

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeImmutable $joinedAt): static
    {
        $this->joinedAt = $joinedAt;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setOwner($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getOwner() === $this) {
                $post->setOwner(null);
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
            $postReaction->setOwner($this);
        }

        return $this;
    }

    public function removePostReaction(PostReaction $postReaction): static
    {
        if ($this->postReactions->removeElement($postReaction)) {
            // set the owning side to null (unless already changed)
            if ($postReaction->getOwner() === $this) {
                $postReaction->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostAudience>
     */
    public function getPostAudiences(): Collection
    {
        return $this->postAudiences;
    }

    public function addPostAudience(PostAudience $postAudience): static
    {
        if (!$this->postAudiences->contains($postAudience)) {
            $this->postAudiences->add($postAudience);
            $postAudience->addUser($this);
        }

        return $this;
    }

    public function removePostAudience(PostAudience $postAudience): static
    {
        if ($this->postAudiences->removeElement($postAudience)) {
            $postAudience->removeUser($this);
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getLastLoggedInAt(): ?\DateTimeImmutable
    {
        return $this->lastLoggedInAt;
    }

    public function setLastLoggedInAt(?\DateTimeImmutable $lastLoggedInAt): static
    {
        $this->lastLoggedInAt = $lastLoggedInAt;

        return $this;
    }

    public function getLastLinkRequestedAt(): ?\DateTimeImmutable
    {
        return $this->lastLinkRequestedAt;
    }

    public function setLastLinkRequestedAt(?\DateTimeImmutable $lastLinkRequestedAt): static
    {
        $this->lastLinkRequestedAt = $lastLinkRequestedAt;

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
            $comment->setOwner($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getOwner() === $this) {
                $comment->setOwner(null);
            }
        }

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return array(
            "id" => $this->getId(),
            "username" => $this->getUsername(),
            "email" => $this->getEmail(),
            "password" => $this->getPassword(),
            "roles" => $this->getRoles(),
            "joinedAt" => $this->getJoinedAt(),
            "slug" => $this->getSlug(),
            "posts" => $this->getPosts(),
            "postAudiences" => $this->getPostAudiences(),
            "postReactions" => $this->getPostReactions(),
            "comments" => $this->getComments(),
            "gender" => $this->getGender(),
        );
    }

    /**
     * @return Collection<int, CommentReaction>
     */
    public function getCommentReactions(): Collection
    {
        return $this->commentReactions;
    }

    public function addCommentReaction(CommentReaction $commentReaction): static
    {
        if (!$this->commentReactions->contains($commentReaction)) {
            $this->commentReactions->add($commentReaction);
            $commentReaction->setOwner($this);
        }

        return $this;
    }

    public function removeCommentReaction(CommentReaction $commentReaction): static
    {
        if ($this->commentReactions->removeElement($commentReaction)) {
            // set the owning side to null (unless already changed)
            if ($commentReaction->getOwner() === $this) {
                $commentReaction->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HiddenComment>
     */
    public function getHiddenComments(): Collection
    {
        return $this->hiddenComments;
    }

    public function addHiddenComment(HiddenComment $hiddenComment): static
    {
        if (!$this->hiddenComments->contains($hiddenComment)) {
            $this->hiddenComments->add($hiddenComment);
            $hiddenComment->setUser($this);
        }

        return $this;
    }

    public function removeHiddenComment(HiddenComment $hiddenComment): static
    {
        if ($this->hiddenComments->removeElement($hiddenComment)) {
            // set the owning side to null (unless already changed)
            if ($hiddenComment->getUser() === $this) {
                $hiddenComment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Warning>
     */
    public function getWarnings(): Collection
    {
        return $this->warnings;
    }

    public function addWarning(Warning $warning): static
    {
        if (!$this->warnings->contains($warning)) {
            $this->warnings->add($warning);
            $warning->setUser($this);
        }

        return $this;
    }

    public function removeWarning(Warning $warning): static
    {
        if ($this->warnings->removeElement($warning)) {
            // set the owning side to null (unless already changed)
            if ($warning->getUser() === $this) {
                $warning->setUser(null);
            }
        }

        return $this;
    }

    public function getBlockedTill(): ?\DateTimeImmutable
    {
        return $this->blockedTill;
    }

    public function setBlockedTill(?\DateTimeImmutable $blockedTill): static
    {
        $this->blockedTill = $blockedTill;

        return $this;
    }

    /**
     * @return Collection<int, Block>
     */
    public function getBlockedUsers(): Collection
    {
        return $this->blockedUsers;
    }

    public function addBlockedUser(Block $blockedUser): static
    {
        if (!$this->blockedUsers->contains($blockedUser)) {
            $this->blockedUsers->add($blockedUser);
            $blockedUser->setBlockedUser($this);
        }

        return $this;
    }

    public function removeBlockedUser(Block $blockedUser): static
    {
        if ($this->blockedUsers->removeElement($blockedUser)) {
            // set the owning side to null (unless already changed)
            if ($blockedUser->getBlockedUser() === $this) {
                $blockedUser->setBlockedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Block>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function addBlock(Block $block): static
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
            $block->setBlockingUser($this);
        }

        return $this;
    }

    public function removeBlock(Block $block): static
    {
        if ($this->blocks->removeElement($block)) {
            // set the owning side to null (unless already changed)
            if ($block->getBlockingUser() === $this) {
                $block->setBlockingUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Snooze>
     */
    public function getSnoozedUsers(): Collection
    {
        return $this->snoozedUsers;
    }

    public function addSnoozedUser(Snooze $snoozedUser): static
    {
        if (!$this->snoozedUsers->contains($snoozedUser)) {
            $this->snoozedUsers->add($snoozedUser);
            $snoozedUser->setSnoozedUser($this);
        }

        return $this;
    }

    public function removeSnoozedUser(Snooze $snoozedUser): static
    {
        if ($this->snoozedUsers->removeElement($snoozedUser)) {
            // set the owning side to null (unless already changed)
            if ($snoozedUser->getSnoozedUser() === $this) {
                $snoozedUser->setSnoozedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Snooze>
     */
    public function getSnoozes(): Collection
    {
        return $this->snoozes;
    }

    public function addSnooze(Snooze $snooze): static
    {
        if (!$this->snoozes->contains($snooze)) {
            $this->snoozes->add($snooze);
            $snooze->setSnoozingUser($this);
        }

        return $this;
    }

    public function removeSnooze(Snooze $snooze): static
    {
        if ($this->snoozes->removeElement($snooze)) {
            // set the owning side to null (unless already changed)
            if ($snooze->getSnoozingUser() === $this) {
                $snooze->setSnoozingUser(null);
            }
        }

        return $this;
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
            $hiddenPost->setUser($this);
        }

        return $this;
    }

    public function removeHiddenPost(HiddenPost $hiddenPost): static
    {
        if ($this->hiddenPosts->removeElement($hiddenPost)) {
            // set the owning side to null (unless already changed)
            if ($hiddenPost->getUser() === $this) {
                $hiddenPost->setUser(null);
            }
        }

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): static
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;
        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): static
    {
        $this->imageSize = $imageSize;
        return $this;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

}
