<?php

namespace App\MessageHandler;

use App\Message\AddChatMessage;
use ModelflowAi\Core\AIRequestHandlerInterface;
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
        private AIRequestHandlerInterface $aiRequestHandler,
        private HubInterface $hub,
        private Environment $twig,
    ) {
    }

    public function __invoke(AddChatMessage $message): void
    {
        $this->hub->publish(new Update(
            'chat',
            $this->twig->render('chat/message.stream.html.twig', [
                'message' => $message->content,
                'role' => $message->role->value,
            ]),
        ));

        $response = $this->aiRequestHandler->createChatRequest(
            new AIChatMessage(AIChatMessageRoleEnum::USER, $message->content)
        )->build()->execute();

        $this->hub->publish(new Update(
            'chat',
            $this->twig->render('chat/message.stream.html.twig', [
                'message' => $response->getMessage()->content,
                'role' => $response->getMessage()->role->value,
            ]),
        ));
    }
}
