<?php

namespace App\MessageHandler;

use App\Criteria\ModelCriteria;
use App\Entity\ChatMessage;
use App\Message\AddChatMessage;
use App\Repository\ChatRepository;
use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\PromptTemplate\Chat\AIChatMessage;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;
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
        $chat->addMessage(
            $message->role,
            $message->content,
        );

        $this->hub->publish(new Update(
            'chat::'.$chat->getUuid(),
            $this->twig->render('chat/message.stream.html.twig', [
                'message' => $message->content,
                'role' => $message->role->value,
                'model' => $chat->getModel(),
            ]),
        ));

        $messages = [];
        /** @var ChatMessage $chatMessage */
        foreach ($chat->getMessages() as $chatMessage) {
            $messages[] = new AIChatMessage($chatMessage->getRole(), $chatMessage->getContent());
        }

        /** @var AIChatResponse $response */
        $response = $this->aiRequestHandler->createChatRequest(
            ...$messages,
        )->addCriteria(ModelCriteria::from($chat->getModel()))->build()->execute();

        $chat->addMessage(
            AIChatMessageRoleEnum::ASSISTANT,
            $response->getMessage()->content,
        );

        $this->hub->publish(new Update(
            'chat::'.$chat->getUuid(),
            $this->twig->render('chat/message.stream.html.twig', [
                'message' => $response->getMessage()->content,
                'role' => $response->getMessage()->role->value,
                'model' => $chat->getModel(),
            ]),
        ));
        $this->repository->flush();

        if (null !== $chat->getTitle()) {
            return;
        }

        /** @var AIChatResponse $response */
        $response = $this->aiRequestHandler->createChatRequest(
            ...[
                ...$messages,
                new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'Having the conversation above. Please create a title for it! Response with the title only no prose, no "Tile: " and no quotes.'),
            ],
        )->addCriteria(ModelCriteria::from('llama2'))->build()->execute();

        $chat->setTitle($response->getMessage()->content);
        $this->repository->flush();
    }
}
