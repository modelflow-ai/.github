<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity]
class Chat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true, nullable: false)]
    private string $uuid;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $model;

    #[ORM\Column(nullable: false)]
    private bool $toolsEnabled;

    #[ORM\Column]
    private \DateTimeImmutable $lastUsedAt;

    /**
     * @var Collection<int, ChatMessage>
     */
    #[ORM\OneToMany(mappedBy: 'chat', targetEntity: ChatMessage::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $messages;

    public function __construct(string $uuid, string $model)
    {
        $this->uuid = $uuid;
        $this->model = $model;
        $this->lastUsedAt = new \DateTimeImmutable();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function isToolsEnabled(): bool
    {
        return $this->toolsEnabled;
    }

    public function setToolsEnabled(bool $toolsEnabled): self
    {
        $this->toolsEnabled = $toolsEnabled;

        return $this;
    }

    public function getLastUsedAt(): \DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @param UploadedFile[] $files
     */
    public function addMessage(
        AIChatMessageRoleEnum $role,
        string $content,
        array $files = [],
    ): ChatMessage {
        $message = new ChatMessage($this, $role, $content, $files);
        $this->messages->add($message);
        $this->lastUsedAt = new \DateTimeImmutable();

        return $message;
    }
}
