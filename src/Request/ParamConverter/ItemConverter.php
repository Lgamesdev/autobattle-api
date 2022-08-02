<?php

declare(strict_types=1);

namespace App\Request\ParamConverter;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ItemConverter implements ParamConverterInterface
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @param ParamConverter $configuration
     * @return void
     */
    public function apply(Request $request, ParamConverter $configuration): void
    {
        if(!$request->attributes->has('id')) {
            return;
        }

        $object = $this->entityManager
            ->getRepository($configuration->getClass())
            ->find($request->attributes->get('id'));

        $request->attributes->set($configuration->getName(), $object);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === User::class;
    }
}