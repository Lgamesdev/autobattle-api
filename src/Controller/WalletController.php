<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/user/wallet', name: 'api_user_wallet_')]
class WalletController
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->tokenStorage = $storage;
    }

    #[Route(name: 'get', methods: [Request::METHOD_GET])]
    public function getUserWallet(WalletRepository $repository,
                                  SerializerInterface $serializer): JsonResponse
    {
        $character = $this->getCurrentUser()->getCharacter();

        $wallet = $repository->findCharacterWallet($character);

        return new JsonResponse(
            $serializer->serialize($wallet, 'json', ['groups' => 'wallet']),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route(name: 'put', methods: [Request::METHOD_PUT])]
    public function updateCurrency(
        Wallet $currency,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator): JsonResponse
    {
        $errors = $validator->validate($currency);

        if($errors->count() > 0) {
            return new JsonResponse(
                $serializer->serialize($errors, 'json'),
                Response::HTTP_BAD_REQUEST,
                [],
                true);
        }

        $character = $this->getCurrentUser()->getCharacter();

        $character->addCurrency($currency);

//        /** @var Currency $currency */
//        $currency = $serializer->deserialize(
//            $request->getContent(),
//            Currency::class,
//            'json',
//            [AbstractNormalizer::OBJECT_TO_POPULATE => $currency]
//        );

        $entityManager->persist($character);
        $entityManager->flush();

        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }

    public function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user;
    }
}