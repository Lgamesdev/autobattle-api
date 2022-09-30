<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(name: 'api_auth_')]
final class AuthController extends AbstractController
{
    #[Route('/register', name: 'register', methods: Request::METHOD_POST)]
    public function register(Request $request,
                            SerializerInterface $serializer,
                            ValidatorInterface $validator,
                            UserPasswordHasherInterface $userPasswordHasher,
                            EntityManagerInterface $entityManager,
                            JWTTokenManagerInterface $JWTTokenManager,
                            RefreshTokenGeneratorInterface $refreshTokenManager) : JsonResponse
    {
        /** @var User $user */
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $validator->validate($user);

        if(count($errors) > 0) {
            return new JsonResponse(
                $serializer->serialize($errors, 'json'),
                Response::HTTP_BAD_REQUEST,
                [],
                true);
        }

        $user->setPassword($userPasswordHasher->hashPassword($user, $user->getPassword()));

        /** @var array<array-key, Currency> $currencyTypes */
        $currencyTypes = $entityManager->getRepository(Currency::class)->findAllIndexed();

        $user->getCharacter()->currency($currencyTypes['Gold'], rand(125, 175));
        $user->getCharacter()->currency($currencyTypes['Crystal'], rand(35, 65));

        $entityManager->persist($user);

        $jwtToken = $JWTTokenManager->create($user);
        $refreshToken = $refreshTokenManager->createForUserWithTtl($user,2592000);

        $entityManager->persist($refreshToken);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize([
                'user' => $user->getUsername(),
                'token' => $jwtToken,
                'refresh_token' => $refreshToken->getRefreshToken(),
                'refresh_token_expiration' => $refreshToken->getValid(),
                'playerConf' => $user->getCharacter()->getConf()
            ], 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }
}