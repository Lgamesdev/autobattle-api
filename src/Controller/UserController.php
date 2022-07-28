<?php

namespace App\Controller;

use App\Entity\Body;
use App\Entity\CurrencyType;
use App\Entity\User;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[Route('/body', name: 'post_body', methods: Request::METHOD_POST)]
    public function postUserBody(Request $request,
                             SerializerInterface $serializer,
                             ValidatorInterface $validator,
                             EntityManagerInterface $entityManager) : JsonResponse
    {
        var_dump($request->getContent());

        /** @var Body $body */
        $body = $serializer->deserialize($request->getContent(), Body::class, 'json');
        $body->setUser($this->getCurrentUser());


        $errors = $validator->validate($body);

        if (count($errors) > 0) {
            return new JsonResponse(
                $serializer->serialize($errors, 'json'),
                Response::HTTP_BAD_REQUEST,
                [],
                true);
        }

        $entityManager->persist($body);

        return new JsonResponse($body->toArray(), Response::HTTP_CREATED);
    }

    public function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user;
    }

}