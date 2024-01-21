<?php

namespace ModelflowAi\Integration\Symfony\Command;

use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\Request\Criteria\PrivacyRequirement;
use ModelflowAi\PromptTemplate\Chat\AIChatMessage;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;
use ModelflowAi\PromptTemplate\ChatPromptTemplate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ChatCommand extends Command
{
    public function __construct(
        private AIRequestHandlerInterface $requestHandler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->requestHandler->createChatRequest(...ChatPromptTemplate::create(
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'You are an {feeling} bot'),
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'Hello {where}!'),
        )->format(['where' => 'world', 'feeling' => 'angry']))
            ->addCriteria(PrivacyRequirement::HIGH)
            ->build()
            ->execute();

        $output->writeln($response->getMessage()->content);

        return 0;
    }
}
