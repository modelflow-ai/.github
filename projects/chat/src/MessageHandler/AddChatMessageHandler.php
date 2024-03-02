<?php

namespace App\MessageHandler;

use App\Controller\ChatController;
use App\Entity\ChatMessage;
use App\Message\AddChatMessage;
use App\Repository\ChatRepository;
use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\ImageBase64Part;
use ModelflowAi\Core\Request\Message\TextPart;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIChatResponseStream;
use ModelflowAi\Integration\Symfony\Criteria\ModelCriteria;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Environment;

#[AsMessageHandler]
class AddChatMessageHandler
{
    public function __construct(
        private ChatRepository $repository,
        private AIRequestHandlerInterface $aiRequestHandler,
        private HubInterface $hub,
        private Environment $twig,
    ) {
    }

    public function __invoke(AddChatMessage $message): void
    {
        $chat = $this->repository->getOneBy(['uuid' => $message->uuid]);
        $chatMessage = $chat->addMessage(
            $message->role,
            $message->content,
            [$message->file],
        );

        $this->hub->publish(new Update(
            'chat::'.$chat->getUuid(),
            $this->twig->render('chat/message.stream.html.twig', [
                'content' => $chatMessage->getContent(),
                'role' => $chatMessage->getRole()->value,
                'model' => $chat->getModel(),
                'image' => $chatMessage->getImage(),
            ]),
        ));

        $messages = [];
        /** @var ChatMessage $chatMessage */
        foreach ($chat->getMessages() as $chatMessage) {
            $parts = [
                TextPart::create($chatMessage->getContent()),
            ];

            foreach ($chatMessage->getFiles() as $file) {
                $parts[] = new ImageBase64Part($file->getContent());
            }

            $messages[] = new AIChatMessage($chatMessage->getRole(), $parts);
        }

        /** @var AIChatResponseStream $response */
        $response = $this->aiRequestHandler->createChatRequest(
            ...$messages,
        )
            ->addCriteria(ModelCriteria::from($chat->getModel()))
            ->streamed()
            ->build()
            ->execute();

        $uuid = Uuid::uuid4()->toString();

        foreach ($response->getMessageStream() as $index => $message) {
            if (0 === $index) {
                $this->hub->publish(new Update(
                    'chat::'.$chat->getUuid(),
                    $this->twig->render('chat/streamed-message-container.html.twig', [
                        'uuid' => $uuid,
                        'content' => $message->content,
                        'role' => $message->role->value,
                        'model' => $chat->getModel(),
                    ]),
                ));

                continue;
            }

            $this->hub->publish(new Update(
                'message::'.$uuid,
                $this->twig->render('chat/streamed-message.html.twig', [
                    'uuid' => $uuid,
                    'content' => $message->content,
                ]),
            ));
        }

        $message = $response->getMessage();
        $chat->addMessage(
            $message->role,
            $message->content,
        );
        $this->repository->flush();

        if (null !== $chat->getTitle()) {
            return;
        }

        /** @var AIChatResponse $response */
        $response = $this->aiRequestHandler->createChatRequest(
            ...[
                ...$messages,
                new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'Having the conversation above. Please create a title for it! Response with the title only no prose, no "Tile: " and no quotes. Keep the original language!'),
            ],
        )->addCriteria(ModelCriteria::from(ChatController::DEFAULT_MODEL))->build()->execute();

        $chat->setTitle($response->getMessage()->content);
        $this->repository->flush();
    }
}
