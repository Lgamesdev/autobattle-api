<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\CharacterEquipmentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/user/equipments', name: 'api_user_equipments_')]
class EquipmentController
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->tokenStorage = $storage;
    }

    #[Route(name: 'get', methods: [Request::METHOD_GET])]
    public function getUserEquipments(CharacterEquipmentRepository $repository,
                                  SerializerInterface $serializer): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        $equipments = $repository->findCharacterEquipments($character);

        return new JsonResponse(
            $serializer->serialize($equipments, 'json', ['groups' => 'characterEquipment']),
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