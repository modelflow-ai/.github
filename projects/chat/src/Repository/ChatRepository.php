<?php

namespace App\Repository;

use App\Entity\Chat;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ChatRepository
{
    /**
     * @var EntityRepository<Chat>
     */
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

    public function remove(Chat $chat): void
    {
        $this->entityManager->remove($chat);
    }

    /**
     * @param array{
     *     uuid: string,
     * } $criteria
     */
    public function getOneBy(array $criteria): Chat
    {
        /** @var Chat|null $entity */
        $entity = $this->entityRepository->findOneBy($criteria);
        if (!$entity) {
            throw new \RuntimeException('Entity not found');
        }

        return $entity;
    }

    /**
     * @return array{
     *     uuid: string,
     *     title: string,
     * }
     */
    public function listChats(): array
    {
        return $this->entityRepository->createQueryBuilder('chat')
            ->select('chat.uuid', 'chat.title')
            ->orderBy('chat.lastUsedAt', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
