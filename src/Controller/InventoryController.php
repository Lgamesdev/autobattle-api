<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\InventoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/user/inventory', name: 'api_inventory_')]
class InventoryController
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->tokenStorage = $storage;
    }

    #[Route(name: 'get', methods: [Request::METHOD_GET])]
    public function getUserInventory(InventoryRepository $inventoryRepository,
                                  SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getCurrentUser();

        $inventory = $inventoryRepository->findCharacterInventory($user);

        return new JsonResponse(
            $serializer->serialize($inventory, 'json', ['groups' => 'inventory']),
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