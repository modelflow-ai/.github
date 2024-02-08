<?php

namespace App\Repository;

use App\Entity\Chat;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ChatRepository
{
    private EntityRepository $entityRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->entityRepository = $entityManager->getRepository(Chat::class);
    }

    public function add(Chat $chat): void
    {
        $this->entityManager->persist($chat);
    }

    public function getOneBy(array $criteria): Chat
    {
        /** @var Chat|null $entity */
        $entity = $this->entityRepository->findOneBy($criteria);
        if (!$entity) {
            throw new \RuntimeException('Entity not found');
        }

        return $entity;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
