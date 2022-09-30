<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\CharacterEquipmentRepository;
use App\Repository\EquipmentRepository;
use App\Repository\ItemRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/shop', name: 'api_shop_')]
class ShopController
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->tokenStorage = $storage;
    }

    #[Route(name: 'get', methods: [Request::METHOD_GET])]
    public function getShopList(ItemRepository $repository,
                                  SerializerInterface $serializer): JsonResponse
    {
        $items = $repository->findAll();

        return new JsonResponse(
            $serializer->serialize($items, 'json', SerializationContext::create()->setGroups(['shopList'])),
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