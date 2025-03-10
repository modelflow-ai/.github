<?php

namespace App\MessageHandler;

use App\Controller\ChatController;
use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Message\AddChatMessage;
use App\Repository\ChatRepository;
use ModelflowAi\Chat\AIChatRequestHandlerInterface;
use ModelflowAi\Chat\Request\Builder\AIChatRequestBuilder;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Request\Message\ImageBase64Part;
use ModelflowAi\Chat\Request\Message\TextPart;
use ModelflowAi\Chat\Request\Message\ToolCallsPart;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\Chat\ToolInfo\ToolExecutorInterface;
use ModelflowAi\Integration\Symfony\Criteria\ModelCriteria;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Environment;

#[AsMessageHandler]
class AddChatMessageHandler
{
    /**
     * @param array<string, array{0: object, 1: string}> $tools
     */
    public function __construct(
        private ChatRepository $repository,
        private AIChatRequestHandlerInterface $aiRequestHandler,
        private ToolExecutorInterface $toolExecutor,
        private HubInterface $hub,
        private Environment $twig,
        private array $tools,
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

        $this->hub->publish(
            new Update(
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
                $parts[] = new ImageBase64Part($file->getContent(), $file->getMimeType());
            }

            $messages[] = new AIChatMessage($chatMessage->getRole(), $parts);
        }

        /** @var AIChatRequestBuilder $requestBuilder */
        $requestBuilder = $this->aiRequestHandler->createStreamedRequest(
            ...$messages,
        )->addCriteria(ModelCriteria::from($chat->getModel()));

        if ($message->enableTools) {
            foreach ($this->tools as $name => $tool) {
                $requestBuilder->tool($name, $tool[0], $tool[1]);
            }
        }

        /** @var AIChatResponseStream $response */
        $response = $requestBuilder->execute();

        $response = $this->handleResponses($chat, $response, $requestBuilder);

        $message = $response->getMessage();
        $chat->addMessage(
            $message->role,
            $message->content,
        );
        $this->repository->flush();

        if (null !== $chat->getTitle()) {
            return;
        }

        $response = $this->aiRequestHandler->createRequest(...[
            ...$messages,
            new AIChatMessage(
                AIChatMessageRoleEnum::SYSTEM,
                'Having the conversation above. Please create a title for it! Response with the title only no prose, no "Tile: " and no quotes. Keep the original language!',
            ),
        ])->addCriteria(ModelCriteria::from(ChatController::DEFAULT_MODEL))->execute();

        $chat->setTitle($response->getMessage()->content);
        $this->repository->flush();
    }

    private function handleResponses(Chat $chat, AIChatResponseStream $response, AIChatRequestBuilder $builder): AIChatResponseStream
    {
        $uuid = Uuid::uuid4()->toString();

        $responses = $response->getMessageStream();
        foreach ($responses as $index => $message) {
            if (null !== $message->toolCalls && 0 < \count($message->toolCalls)) {
                $toolCalls = $message->toolCalls;
                $additionalMessages = [];
                $responses->next();

                while ($responses->valid()) {
                    $nextMessage = $responses->current();
                    $toolCalls = array_merge($toolCalls, $nextMessage->toolCalls);
                    $responses->next();
                }

                foreach ($toolCalls as $toolCall) {
                    $additionalMessages[] = $this->toolExecutor->execute($builder->build(), $toolCall);
                }

                $builder->addMessage(
                    new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, ToolCallsPart::create($toolCalls)),
                );

                $builder->addMessages($additionalMessages);

                /** @var AIChatResponseStream $response */
                $response = $builder->build()
                    ->execute();

                return $this->handleResponses($chat, $response, $builder);
            }

            if (0 === $index) {
                $this->hub->publish(
                    new Update(
                        'chat::'.$chat->getUuid(),
                        $this->twig->render('chat/streamed-message-container.html.twig', [
                            'uuid' => $uuid,
                            'content' => $message->content,
                            'role' => $message->role->value,
                            'model' => $chat->getModel(),
                        ]),
                    ),
                );

                continue;
            }

            $this->hub->publish(
                new Update(
                    'message::'.$uuid,
                    $this->twig->render('chat/streamed-message.html.twig', [
                        'uuid' => $uuid,
                        'content' => $message->content,
                    ]),
                ),
            );
        }

        return $response;
    }
}
