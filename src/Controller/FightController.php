<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Fight;
use App\Entity\User;
use App\Exception\FightException;
use App\Repository\FightRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/user/fight', name: 'api_user_fight')]
class FightController
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->tokenStorage = $storage;
    }

    #[Route(name: 'get', methods: [Request::METHOD_GET])]
    public function fight(SerializerInterface $serializer,
                          FightRepository $fightRepository,
                          EntityManagerInterface $entityManager): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        try {
            $fight = $fightRepository->createFight($character);
        } catch (FightException $e) {
            return new JsonResponse(
                $serializer->serialize($e, 'json'),
                Response::HTTP_BAD_REQUEST,
                [],
                true);
        }

        $entityManager->persist($fight);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize($fight, 'json', Fight::getSerializationContext()),
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