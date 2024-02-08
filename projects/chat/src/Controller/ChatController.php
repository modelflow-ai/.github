<?php

namespace App\Controller;

use App\Message\AddChatMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class ChatController extends AbstractController
{
    #[Route('/', name: 'app_chat')]
    public function index(Request $request, MessageBusInterface $messageBus): Response
    {
        $form = $this->createFormBuilder([], ['attr' => ['enctype' => 'multipart/form-data']])
            ->add('file', FileType::class, [
                'label' => 'Upload file',
                'row_attr' => [
                    'class' => 'mr-4',
                ],
                'label_attr' => [
                    'class' => 'cursor-pointer bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-full',
                ],
                'attr' => [
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

        $emptyForm = clone $form;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $messageBus->dispatch(new AddChatMessage($data['message']));

            $form = $emptyForm;
        }

        return $this->render('chat/index.html.twig', [
            'form' => $form,
            'controller_name' => 'ChatController',
        ]);
    }
}
