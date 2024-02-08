<?php

namespace App\Entity;

use App\Repository\ChatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;

#[ORM\Entity]
class Chat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true, nullable: false)]
    private string $uuid;

    #[ORM\Column(length: 255, nullable: false)]
    private string $title = '';

    #[ORM\OneToMany(mappedBy: 'chat', targetEntity: ChatMessage::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $messages;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
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

    /**
     * @return Collection<int, ChatMessage>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(
        AIChatMessageRoleEnum $role,
        string $content,
    ): ChatMessage {
        $message = new ChatMessage($this, $role, $content);
        $this->messages->add($message);

        return $message;
    }
}
