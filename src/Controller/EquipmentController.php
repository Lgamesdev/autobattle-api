<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Body;
use App\Entity\CharacterEquipment;
use App\Entity\Equipment;
use App\Entity\User;
use App\Enum\EquipmentSlot;
use App\Repository\CharacterEquipmentRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

        $equipments = $character->getGear()->getCharacterEquipments();

        return new JsonResponse(
            $serializer->serialize($equipments, 'json', SerializationContext::create()->setGroups(['characterEquipment'])),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/equip/{id}', name: 'put', methods: [Request::METHOD_PUT])]
    public function equipCharacterEquipment(CharacterEquipment     $characterEquipment,
                                            EntityManagerInterface $entityManager): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        dd($characterEquipment);

        $character->addCharacterEquipment($characterEquipment);

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