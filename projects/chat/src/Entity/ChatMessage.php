<?php

namespace App\Entity;

use App\Repository\ChatMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;

#[ORM\Entity]
class ChatMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private Chat $chat;

    #[ORM\Column(length: 32, nullable: false)]
    private string $role;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $content;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Chat $chat,
        AIChatMessageRoleEnum $role,
        string $content,
    ) {
        $this->chat = $chat;
        $this->role = $role->value;
        $this->content = $content;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChat(): Chat
    {
        return $this->chat;
    }

    public function getRole(): AIChatMessageRoleEnum
    {
        return AIChatMessageRoleEnum::from($this->role);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
