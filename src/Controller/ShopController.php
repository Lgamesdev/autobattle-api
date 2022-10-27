<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BaseCharacterItem;
use App\Entity\BaseItem;
use App\Entity\Equipment;
use App\Entity\Item;
use App\Entity\User;
use App\Repository\CharacterEquipmentRepository;
use App\Repository\EquipmentRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route(name: 'get_shop_list', methods: [Request::METHOD_GET])]
    public function getShopList(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer): JsonResponse
    {
        $items = $entityManager->getRepository(BaseItem::class)->findAll();

        return new JsonResponse(
            $serializer->serialize($items, 'json', SerializationContext::create()->setGroups(['shopList'])),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/buy/{id}', name: 'buy_shop_item', methods: [Request::METHOD_POST])]
    public function buyItem(
        BaseItem $item,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        $characterItem = $character->tryBuy($item);

        if($characterItem == null) {
            return new JsonResponse(
                $serializer->serialize('An error occurred during item buy', 'json'),
                Response::HTTP_BAD_REQUEST,
                [],
                true);
        }

        $entityManager->persist($character);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize($characterItem, 'json', SerializationContext::create()->setGroups(['playerInventory'])),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/sell/{id}', name: 'sell_character_item', methods: [Request::METHOD_PUT])]
    public function sellItem(
        BaseCharacterItem $item,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        $isSelled = $character->sell($item);

        if(!$isSelled) {
            return new JsonResponse(
                $serializer->serialize('An error occurred during item sell', 'json'),
                Response::HTTP_BAD_REQUEST,
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