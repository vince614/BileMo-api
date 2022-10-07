<?php

namespace App\Controller\Api;

use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @param UserRepository $userRepository
     * @param ClientRepository $clientRepository
     */
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ClientRepository $clientRepository)
    {
    }

    #[Route('/api/client/users', name: 'api_users_client_showAll')]
    #[IsGranted('ROLE_ADMIN')]
    public function showAllUsers(): JsonResponse
    {
        $user = $this->getUser();
        $client = $this->clientRepository->findOneBy(['email' => $user->getUserIdentifier()]);
        $users = $this->userRepository->findAllByClientId($client->getId());
        return $this->json($users);
    }

    #[Route('/api/client/user/{userId}', name: 'api_user_client')]
    #[IsGranted('ROLE_ADMIN')]
    public function retreiveUserFromClient(string $userId): JsonResponse
    {
        $user = $this->getUser();
        $client = $this->clientRepository->findOneBy(['email' => $user->getUserIdentifier()]);
        $user = $this->userRepository->findByCliendId($client->getId(), $userId);
        if (!$user) {
            return $this->json([
                'message' => 'User not found',
                'code' => 404
            ], 404);
        }
        return $this->json($user);
    }
}
