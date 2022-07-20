<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CurrencyRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/user', name: 'api_user_')]
class UserController
{
    private TokenStorageInterface $tokenStorage;

    public function __construct( TokenStorageInterface $storage)
    {
        $this->tokenStorage = $storage;
    }

    //TODO : find a method name and $user->function name
    #[Route('/body', name: 'get_body', methods: [Request::METHOD_GET])]
    public function getUserBody(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getCurrentUser();

        return new JsonResponse(
            $serializer->serialize($user->getUserBody(), 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/infos', name: 'get_infos', methods: [Request::METHOD_GET])]
    public function getUserInfos(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getCurrentUser();

        return new JsonResponse(
            $serializer->serialize($user->getUserInfos(), 'json'),
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