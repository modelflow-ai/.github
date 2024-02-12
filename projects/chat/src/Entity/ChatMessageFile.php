<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity]
class ChatMessageFile
{
    public static function fromUploadedFile(
        ChatMessage $message,
        UploadedFile $file,
    ): self {
        return new self(
            $message,
            $file->getFilename(),
            $file->getMimeType(),
            base64_encode(file_get_contents($file->getPathname())),
        );
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private ChatMessage $chatMessage;

    #[ORM\Column(length: 32, nullable: false)]
    private string $filename;

    #[ORM\Column(length: 32, nullable: false)]
    private string $mimeType;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $content;

    public function __construct(
        ChatMessage $chatMessage,
        string $filename,
        string $mimeType,
        string $content,
    ) {
        $this->chatMessage = $chatMessage;
        $this->filename = $filename;
        $this->mimeType = $mimeType;
        $this->content = $content;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChatMessage(): ChatMessage
    {
        return $this->chatMessage;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
