<?php

namespace App\Controller;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;



class RegisterController extends AbstractController
{
    /**
     * Inscription
     * 
     * @OA\Parameter(
     *      name="username",
     *      in="query",
     *      description="Nom d'utilisateur",
     *      @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *      name="password",
     *      in="query",
     *      description="Mot de passe",
     *      @OA\Schema(type="password")
     * )
     * 
     * @OA\Tag(name="Inscription")
     */
    #[Route('/register', name: 'register', methods: 'POST')]
    public function register(Request $request, UserPasswordHasherInterface $passEncoder, EntityManagerInterface $em): JsonResponse

    {
        if ($request->isMethod('post')) {

            $user = new User;
            $user->setUsername($request->query->get('username'));
            $hashedPassword = $passEncoder->hashPassword($user, $request->query->get('pwd'));
            $user->setPassword($hashedPassword);



            // Query the database for a user with the given email
            $u = $em->getRepository(User::class)->findOneBy(['username' => $request->query->get('username')]);

            if ($u) {
                $resultat = ["NOK1"];
                return new JsonResponse($resultat);
            } else {

                $em->persist($user);
                $em->flush();
                if (null === $user->getId()) {
                    $resultat = ["NOK2"];
                    return new JsonResponse($resultat);
                } else {
                    $resultat = ['id_user' => $user->getId()];
                    return new JsonResponse($resultat);
                }
            }
        }
    }
}
