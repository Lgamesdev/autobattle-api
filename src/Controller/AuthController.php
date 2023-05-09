<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\CurrencyType;
use App\Enum\StatType;
use App\Exception\UserCharacterException;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Doctrine\RefreshTokenRepositoryInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Google\Client;
use Google\Exception;
use Google\Service;
use Google\Service\Drive;
use Google\Service\Vault\AccountInfo;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Oauth2;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(name: 'api_auth_')]
final class AuthController extends AbstractController
{
    /*
     * Using Symfony Serializer interface cause JMS serializer don't call constructor
     */
    /**
     * @throws UserCharacterException
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

        $user->getCharacter()->initialize();

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

    /**
     * @throws Exception
     * @throws \Exception
     */
    #[Route('/connect/google', name: 'connect_google')]
    public function connectCheckAction(Request $request,
                                       SerializerInterface $serializer,
                                       ValidatorInterface $validator,
                                       UserPasswordHasherInterface $userPasswordHasher,
                                       EntityManagerInterface $entityManager,
                                       JWTTokenManagerInterface $JWTTokenManager,
                                       RefreshTokenGeneratorInterface $refreshTokenManager): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        if ($content['code']) {
            $client = new Google_Client();
            //$client->useApplicationDefaultCredentials();
            $client->setAuthConfig($this->getParameter('app.googleclientconf'));
            $client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
            $client->addScope(Google_Service_Oauth2::USERINFO_PROFILE);
            $client->addScope(Google_Service_Drive::DRIVE);
            $accessToken = $client->fetchAccessTokenWithAuthCode($content['code']);

            //dd($accessToken);

            if (!isset($accessToken['error'])) {
                $service = new Drive($client);
                $email = $service->about->get(['fields' => 'user'])->getUser()->getEmailAddress();

                // have they logged in with Google before? Easy!
                $existingUser = $entityManager->getRepository(User::class)->findOneByEmail($email);

                //User doesnt exist, we create it !
                if (!$existingUser) {
                    if($content['username'] == null)
                    {
                        return new JsonResponse(
                            $serializer->serialize(new UserCharacterException('auth-0001'), 'json'),
                            Response::HTTP_BAD_REQUEST,
                            [],
                            true);
                    }

                    $existingUser = new User();
                    $existingUser->setUsername($content['username']);
                    $existingUser->setEmail($email);
                    $existingUser->setPassword(random_bytes(10));

                    $errors = $validator->validate($existingUser);

                    if(count($errors) > 0) {
                        return new JsonResponse(
                            $serializer->serialize($errors, 'json'),
                            Response::HTTP_BAD_REQUEST,
                            [],
                            true);
                    }

                    $existingUser->setPassword($userPasswordHasher->hashPassword($existingUser, $existingUser->getPassword()));

                    $existingUser->getCharacter()->initialize();
                }

                $jwtToken = $JWTTokenManager->create($existingUser);
                $refreshToken = $refreshTokenManager->createForUserWithTtl($existingUser,2592000);

                $entityManager->persist($existingUser);
                $entityManager->persist($refreshToken);
                $entityManager->flush();

                return new JsonResponse(
                    $serializer->serialize([
                        'username' => $existingUser->getUsername(),
                        'token' => $jwtToken,
                        'refresh_token' => $refreshToken->getRefreshToken(),
                        'refresh_token_expiration' => $refreshToken->getValid(),
                        'playerConf' => $existingUser->getCharacter()->getConf()
                    ], 'json'),
                    Response::HTTP_OK,
                    [],
                    true
                );
            } else {
                return new JsonResponse(
                    $serializer->serialize($accessToken, 'json'),
                    Response::HTTP_FORBIDDEN,
                    [],
                    true);
            }
        } else {
            return new JsonResponse(
                'No authorization code found',
                Response::HTTP_FORBIDDEN,
                []);
        }
    }

    #[Route('/forgot-pass', name:'forgotten_password', methods: Request::METHOD_GET)]
    public function forgottenPassword(Request $request,
                                      UserRepository $userRepository,
                                      SerializerInterface $serializer,
                                      TokenGeneratorInterface $tokenGenerator,
                                      EntityManagerInterface $entityManager,
                                      SendMailService $mail): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->submit($data);

        if($form->isValid()) {
            //On va chercher l'utilisateur par son email
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            // On vérifie si on a un utilisateur
            if ($user) {
                // On génère un token de réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                // On génère un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('api_auth_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // On crée les données du mail
                $context = compact('url', 'user');

                // Envoi du mail
                $mail->send(
                    'no-reply@lgamesdev.com',
                    $user->getEmail(),
                    'Reset password',
                    'password_reset_email',
                    $context
                );

                return new JsonResponse($serializer->serialize([
                    'resetToken' => $user->getResetToken()
                ], 'json'),
                    Response::HTTP_OK,
                    [],
                    false
                );
            }
            // $user est null
            return new JsonResponse(
                $serializer->serialize([
                    'status' => false,
                    'message' => 'User not found!'
                ], 'json'),
                Response::HTTP_BAD_REQUEST,
                [],
                true
            );
        }

        // form is not valid
        return new JsonResponse(
            $form->getErrors(),
            Response::HTTP_BAD_REQUEST,
            [],
            true
        );
    }

    #[Route('/forgot-pass/{token}', name:'reset_password', methods: Request::METHOD_POST)]
    public function resetPass(
        string $token,
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        // On vérifie si on a ce token dans la base
        $user = $usersRepository->findOneByResetToken($token);

        // On vérifie si l'utilisateur existe

        if($user){
            $form = $this->createForm(ResetPasswordFormType::class);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                // On efface le token
                $user->setResetToken('');


            // On enregistre le nouveau mot de passe en le hashant
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Mot de passe changé avec succès');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/password_reset.html.twig', [
                'passForm' => $form->createView()
            ]);
        }

        // Si le token est invalide on redirige vers le login
        $this->addFlash('danger', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
    }
}