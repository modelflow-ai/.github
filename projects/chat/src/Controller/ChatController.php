<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Message\AddChatMessage;
use App\Repository\ChatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class ChatController extends AbstractController
{
    public const DEFAULT_MODEL = 'llama2';

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ChatRepository $repository,
    ) {
    }

    #[Route('/', name: 'app_create_chat')]
    public function index(Request $request): Response
    {
        $form = $this->createMessageForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $chat = new Chat(Uuid::v4()->toRfc4122(), $request->query->get('model', self::DEFAULT_MODEL));
            $this->repository->add($chat);
            $this->repository->flush();

            $this->messageBus->dispatch(new AddChatMessage($chat->getUuid(), $data['message'], $data['file']));

            return $this->redirectToRoute('app_chat', ['uuid' => $chat->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chat/index.html.twig', [
            'chats' => $this->repository->listChats(),
            'form' => $form,
            'DEFAULT_MODEL' => self::DEFAULT_MODEL,
        ]);
    }

    #[Route('/{uuid}', name: 'app_chat')]
    public function chat(Request $request, string $uuid): Response
    {
        $chat = $this->repository->getOneBy(['uuid' => $uuid]);
        if ($request->query->get('model', $chat->getModel()) !== $chat->getModel()) {
            $chat->setModel($request->query->get('model'));
            $this->repository->flush();

            return $this->redirectToRoute('app_chat', ['uuid' => $chat->getUuid()], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createMessageForm();

        $emptyForm = clone $form;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->messageBus->dispatch(new AddChatMessage($uuid, $data['message'], $data['file']));

            $form = $emptyForm;
        }

        return $this->render('chat/index.html.twig', [
            'chat' => $this->repository->getOneBy(['uuid' => $uuid]),
            'chats' => $this->repository->listChats(),
            'form' => $form,
            'DEFAULT_MODEL' => self::DEFAULT_MODEL,
        ]);
    }

    #[Route('/{uuid}/delete', name: 'app_chat_delete')]
    public function chatDelete(string $uuid): Response
    {
        $chat = $this->repository->getOneBy(['uuid' => $uuid]);

        $this->repository->remove($chat);
        $this->repository->flush();

        return $this->redirectToRoute('app_create_chat');
    }

    private function createMessageForm(): FormInterface
    {
        return $this->createFormBuilder([], ['attr' => ['enctype' => 'multipart/form-data']])
            ->add('file', FileType::class, [
                'label' => 'Upload file',
                'row_attr' => [
                    'class' => 'mr-4',
                ],
                'label_attr' => [
                    'class' => 'cursor-pointer bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-full',
                ],
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'hidden',
                ],
                'required' => false,
            ])
            ->add('message', TextType::class, [
                'label' => false,
                'row_attr' => [
                    'class' => 'flex-1 mr-4',
                ],
                'attr' => [
                    'class' => 'w-full appearance-none rounded-full border border-gray-700 py-2 px-3 focus:outline-none focus:border-blue-500 bg-gray-700',
                    'placeholder' => 'Type your message...',
                ],
            ])
            ->add('send', SubmitType::class, [
                'attr' => [
                    'class' => 'bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-full',
                ],
            ])
            ->getForm();
    }
}
