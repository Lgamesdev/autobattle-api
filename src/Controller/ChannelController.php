<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\InventoryRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\WebLink\Link;

#[Route('/user/channel', name: 'api_channel_')]
class ChannelController extends AbstractController
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $storage)
    {
        $this->tokenStorage = $storage;
    }

    #[Route('/', name: 'home')]
    public function getChannels(ChannelRepository $channelRepository, SerializerInterface $serializer): Response
    {
        $channels = $channelRepository->findAll();

        return new JsonResponse(
            $serializer->serialize(['channels' => $channels ?? []], 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route("/chat/{id}", name: "chat")]
    public function chat(
        Request $request,
        Channel $channel,
        MessageRepository $messageRepository,
        SerializerInterface $serializer
    ): Response
    {
        $messages = $messageRepository->findBy([
            'channel' => $channel
        ], ['createdAt' => 'ASC']);

        $hubUrl = $this->getParameter('mercure.default_hub'); // Mercure automatically define this parameter
        $this->addLink($request, new Link('mercure', $hubUrl)); // Use the WebLink Component to add this header to the following response


        return new JsonResponse(
            $serializer->serialize([
                'channel' => $channel,
                'messages' => $messages], 'json'),
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