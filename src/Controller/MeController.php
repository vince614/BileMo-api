<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

final class MeController extends AbstractController
{
    /**
     * Contruct MeController
     * @param Security $security
     */
    public function __construct(private readonly Security $security) {}

    /**
     * Invoke call
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();
        return $this->json($user);
    }
}