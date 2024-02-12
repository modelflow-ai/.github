<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    /**
     * @var Collection<int, ChatMessageFile>
     */
    #[ORM\OneToMany(mappedBy: 'chatMessage', targetEntity: ChatMessageFile::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\Column(length: 255, nullable: false)]
    private string $model;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * @param UploadedFile[] $files
     */
    public function __construct(
        Chat $chat,
        AIChatMessageRoleEnum $role,
        string $content,
        array $files,
    ) {
        $this->chat = $chat;
        $this->role = $role->value;
        $this->content = $content;
        $this->files = new ArrayCollection(
            array_map(fn (UploadedFile $file) => ChatMessageFile::fromUploadedFile($this, $file), array_filter($files)),
        );
        $this->model = $chat->getModel();
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

    /**
     * @return Collection<int, ChatMessageFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return array{
     *     mimeType: string,
     *     content: string,
     * }|null
     */
    public function getImage(): ?array
    {
        $file = $this->files->first();
        if (!$file) {
            return null;
        }

        return [
            'mimeType' => $file->getMimeType(),
            'content' => $file->getContent(),
        ];
    }
}
