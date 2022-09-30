<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\CharacterEquipmentRepository;
use App\Repository\CharacterStatRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/user/stats', name: 'api_user_stats_')]
class StatController
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->tokenStorage = $storage;
    }

    #[Route(name: 'get', methods: [Request::METHOD_GET])]
    public function getCharacterStats(CharacterStatRepository $repository,
                                  SerializerInterface $serializer): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        $stats = $repository->findCharacterStats($character);

        return new JsonResponse(
            $serializer->serialize($stats, 'json', SerializationContext::create()->setGroups(['characterStat'])),
            Response::HTTP_OK,
            [],
            true
        );
    }

    public function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user;
    }
}