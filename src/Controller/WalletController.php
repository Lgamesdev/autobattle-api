<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Currency;
use App\Entity\User;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/wallet', name: 'api_wallet_')]
class WalletController
{
    #[Route(name: 'collection_get', methods: [Request::METHOD_GET])]
    public function collection(CurrencyRepository $currencyRepository, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize($currencyRepository->findAll(), 'json', ['groups' => 'get']),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'item_get', methods: [Request::METHOD_GET])]
    public function item(Currency $currency, SerializerInterface $serializer) : JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize($currency, 'json', ['groups' => 'get']),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route(name: 'item_post', methods: [Request::METHOD_POST])]
    #[ParamConverter('currency', converter: 'api_converter')]
    public function post(
        Currency $currency,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator): JsonResponse
    {
//        /** @var Currency $currency */
//        $currency = $serializer->deserialize($request->getContent(), Currency::class, 'json');
        $currency->setUser($entityManager->getRepository(User::class)->findOneBy([]));

        $errors = $validator->validate($currency);

        if($errors->count() > 0) {
            return new JsonResponse(
                $serializer->serialize($errors, 'json'),
                Response::HTTP_BAD_REQUEST,
                [],
                true);
        }

        $entityManager->persist($currency);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize($currency, 'json', ['groups' => 'get']),
            Response::HTTP_CREATED,
            ["Location" => $urlGenerator->generate('api_wallet_item_get', ['id' => $currency->getId()])],
            true
        );
    }

    #[Route('/{id}', name: 'item_put', methods: [Request::METHOD_PUT])]
    public function put(
        Currency $currency,
        Request $request,
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

        /** @var Currency $currency */
        $currency = $serializer->deserialize(
            $request->getContent(),
            Currency::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currency]
        );

        $entityManager->persist($currency);
        $entityManager->flush();

        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'item_delete', methods: [Request::METHOD_DELETE])]
    public function delete(
        Currency $currency,
        EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($currency);
        $entityManager->flush();

        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }
}