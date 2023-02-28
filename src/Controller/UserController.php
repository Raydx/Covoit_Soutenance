<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncode)
    {
        $this->passwordEncoder = $passwordEncode;
    }

    /**
     * Connexion
     * 
     * @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          example={
     *              "username": "ThierryL",
     *              "password": "cda123"
     *          },
     *          @OA\Schema (
     *              type="object",
     *              @OA\Property(property="status", required=true, description="Event status", type="string"),
     *              @OA\Property(property="comment", required=false, description="Change status comment", type="string"),
     *          )
     *      )    
     * )
     * 
     * 
     * @OA\Tag(name="Authentification")
     */
    #[Route('/login', name: 'login', methods: "POST")]
    public function Authentification(Request $request, ManagerRegistry $doctrine, EntityManagerInterface $em): JsonResponse
    {
        $username = $request->query->get('username');
        $password = $request->query->get('pwd');
        $user = new User;
        $user->setUsername($username);
        $user->setPassword($this->passwordEncoder->hashPassword($user, $password));

        $em = $doctrine->getManager();
        $userRepository = $em->getRepository(User::class);

        $u = $userRepository->findOneBy(['username' => $username]);

        if ($u) {
            if (!$this->passwordEncoder->isPasswordValid($u, $password)) {
                $resultat = ["NOK pwd invalide"];
            } else {
                $randomBytes = random_bytes(16);
                $token = bin2hex($randomBytes);
                $u->setApiToken($token);
                $em->persist($u);
                $em->flush();
                $resultat = ['id_user' => $u->getId(), 'token' => $u->getApiToken()];
            }
        } else {
            $resultat = ["NOK username invalide"];
        }
        $reponse = new JsonResponse($resultat);
        return $reponse;
    }

    /**
     * Déconnexion
     * 
     * @OA\Parameter(
     *      name="token",
     *      in="header",
     *      description="Token unique de l'utilisateur",
     *      @OA\Schema(type="string")
     * )
     * @OA\Tag(name="Authentification")
     */
    #[Route('/logout', name: 'logout', methods: 'POST')]
    public function logout(Request $request, ManagerRegistry $doctrine, EntityManagerInterface $em): JsonResponse
    {

        $token = $request->headers->get("token");

        $u = $em->getRepository(User::class)->findOneBy(['api_token' => $token]);

        $u->setApiToken(null);

        $em->persist($u);
        $em->flush();

        $resultat = ["OK"];
        return new JsonResponse($resultat);
    }
}
