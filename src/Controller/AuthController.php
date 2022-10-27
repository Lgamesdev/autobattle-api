<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\CurrencyType;
use App\Enum\StatType;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(name: 'api_auth_')]
final class AuthController extends AbstractController
{
    /*
     * Using Symfony Serializer interface cause JMS serializer don't call constructor
     */
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

        foreach (StatType::cases() as $statType) {
            $statValue = match ($statType) {
                StatType::HEALTH => rand(90, 110),
                StatType::ARMOR => null,
                StatType::SPEED, StatType::DODGE => rand(4, 8),
                StatType::DAMAGE => rand(8, 12),
                StatType::CRITICAL => rand(6, 12)
            };
            $user->getCharacter()->stat($statType, $statValue);
        }

        foreach (CurrencyType::cases() as $currencyType)
        {
            $user->getCharacter()->currency($currencyType, rand(100, 150));
        }

        $entityManager->persist($user);

        $jwtToken = $JWTTokenManager->create($user);
        $refreshToken = $refreshTokenManager->createForUserWithTtl($user,2592000);

        $entityManager->persist($refreshToken);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize([
                'username' => $user->getUsername(),
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