<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CharacterStat;
use App\Entity\User;
use App\Enum\StatType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

    #[Route(name: 'get_character_stats', methods: [Request::METHOD_GET])]
    public function getCharacterStats(SerializerInterface $serializer): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        return new JsonResponse(
            $serializer->serialize($character->getStats(), 'json', SerializationContext::create()->setGroups(['characterStat'])),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/add', name: 'add_stat_point', methods: [Request::METHOD_PUT])]
    public function addStatPoint(Request $request,
                                 SerializerInterface $serializer,
                                 EntityManagerInterface $entityManager): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();
        $statLabel = json_decode($request->getContent(), true)['statType'];

        $statType = StatType::from($statLabel);

        try {
            $character->addStatPoint($statType);
        } catch (Exception $e) {
            return new JsonResponse(
                $serializer->serialize($e, 'json'),
                Response::HTTP_FORBIDDEN,
                [],
                true);
        }

        $entityManager->persist($character);
        $entityManager->flush();

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT,
            [],
            false
        );
    }

    public function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user;
    }
}