<?php

declare(strict_types=1);

namespace App\Request\ParamConverter;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class PostConverter implements ParamConverterInterface
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     * @param ParamConverter $configuration
     * @return void
     */
    public function apply(Request $request, ParamConverter $configuration): void
    {
        if(!$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        $object = $this->serializer->deserialize(
            $request->getContent(),
            $configuration->getClass(),
            'json');

        $request->attributes->set($configuration->getName(), $object);
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === User::class;
    }
}