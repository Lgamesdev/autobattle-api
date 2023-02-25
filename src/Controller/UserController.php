<?php

namespace App\Controller;

use App\Entity\Body;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/user', name: 'api_user_')]
class UserController
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->tokenStorage = $storage;
    }

    #[Route('/body', name: 'get_body', methods: [Request::METHOD_GET])]
    public function getCharacterBody(SerializerInterface $serializer): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        return new JsonResponse(
            $serializer->serialize($character->getBody(), 'json', SerializationContext::create()->setGroups(['body'])),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/body', name: 'put_body', methods: Request::METHOD_PUT)]
    public function putUserBody(Request $request,
                             SerializerInterface $serializer,
                             ValidatorInterface $validator,
                             EntityManagerInterface $entityManager) : JsonResponse
    {
        /** @var Body $body */
        $body = $serializer->deserialize($request->getContent(), Body::class, 'json');

        $errors = $validator->validate($body);

        if (count($errors) > 0) {
            return new JsonResponse(
                $serializer->serialize($errors, 'json'),
                Response::HTTP_BAD_REQUEST,
                [],
                true);
        }
        $character = $this->getCurrentUser()->getCharacter();

        if($character->isCreationDone())
        {
            return new JsonResponse(
                null,
                Response::HTTP_FORBIDDEN,
                [],
                false);
        }

        $character->setBody($body);
        $character->setCreationDone(true);

        $entityManager->persist($character);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize($character->getBody(), 'json', SerializationContext::create()->setGroups(['body'])),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/progression', name: 'get_progression', methods: [Request::METHOD_GET])]
    public function getUserProgression(SerializerInterface $serializer): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        return new JsonResponse(
            $serializer->serialize(
            [
                'level' => $character->getLevel(),
                'xp' => $character->getExperience(),
                'statPoint' => $character->getStatPoints(),
                'ranking' => $character->getRanking(),
            ], 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/configuration', name: 'get_configuration', methods: [Request::METHOD_GET])]
    public function getUserConfiguration(SerializerInterface $serializer): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        return new JsonResponse(
            $serializer->serialize($character->getConf(), 'json'),
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