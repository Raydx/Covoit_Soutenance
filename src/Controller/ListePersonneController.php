<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\Personne;
use App\Entity\User;
use App\Entity\Car;
use App\Entity\Model;
use App\Repository\PersonneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;




class ListePersonneController extends AbstractController
{
    /**
     * Liste des inscrits
     * 
     * @OA\Tag(name="Personne")
     */
    #[Route('/liste/personne', name: 'listePersonne', methods: "GET")]
    public function listePersonne(PersonneRepository $repoPers, SerializerInterface $serializerInterface): JsonResponse
    {
        $liste = $repoPers->findAll();
        //ya une histoire de groupes à faire, renseigne toi dessus
        $listeJson = $serializerInterface->serialize($liste, 'json', ['groups' => ['info']]);

        return new JsonResponse($listeJson, 200, [], true);
    }

    /**
     * Méthode qui affiche le profil d'une personne
     * 
     * @OA\Tag(name="Personne")
     */
    #[Route('/liste/personne/{id}', name: 'selectPersonne', methods: "GET")]
    public function listeUnePersonne(int $id, PersonneRepository $repoPers, SerializerInterface $serializerInterface): JsonResponse
    {
        $personne = $repoPers->find($id);
        //ya une histoire de groupes à faire, renseigne toi dessus
        if (!$personne) {
            return new JsonResponse(['error' => 'Utilisateur inconnu...'], 404);
        } else {

            $personneJson = $serializerInterface->serialize($personne, 'json', ['groups' => ['info']]);
            $personneResu = new JsonResponse($personneJson, 200, [], true);
            $cars = $personne->getVoiture();

            $array = array();
            foreach ($cars as $car) {
                $voiture = array([
                    'cars' => $car,
                    'id' => $car->getId(),
                    'matricule' => $car->getMatricule(),
                    'color' => $car->getColor(),
                    'places' => $car->getPlaces(),
                    'marque' => $car->getModele()->getName()
                ]);
                $array[] = $voiture;
            }

            $contenu1 = json_decode($personneResu->getContent(), true);

            $fusion = array_merge($contenu1, $array);

            $reponse = new JsonResponse($fusion);

            return $reponse;
        }
    }

    /**
     * Méthode qui liste les voitures d'une personne
     * 
     * @OA\Tag(name="Personne")
     */
    #[Route('/liste/personne/voiture/{id}', name: 'selectPersonneVoiture', methods: "GET")]
    public function listePersonneVoiture(int $id, PersonneRepository $repoPers, EntityManagerInterface $em): JsonResponse
    {
        $personne = $repoPers->find($id);
        //ya une histoire de groupes à faire, renseigne toi dessus
        if (!$personne) {
            return new JsonResponse(['error' => 'Utilisateur inconnu...'], 404);
        } else {


            $cars = $personne->getVoiture();


            $array = array();

            foreach ($cars as $car) {
                $voiture = array([
                    'cars' => $car,
                    'id' => $car->getId(),
                    'matricule' => $car->getMatricule(),
                    'color' => $car->getColor(),
                    'places' => $car->getPlaces(),
                    'marque' => $car->getModele()->getName()
                ]);
                $array[] = $voiture;
            }
            $response = new JsonResponse($array);

            return $response;
        }
    }

    /**
     * Méthode qui supprime un inscrit
     * 
     * @OA\Tag(name="Personne")
     */
    #[Route('/deletePersonne/{id}', name: 'delete_personne', methods: "DELETE")]
    public function deletePersonne(int $id, PersonneRepository $doctrine, EntityManagerInterface $em): JsonResponse
    {
        $personne = $doctrine->find($id);

        if (!$personne) {
            return new JsonResponse(['error' => 'Personne inconnue...'], 404);
        } else {
            $em->remove($personne);
            $em->flush();

            return new JsonResponse(['message' => 'Personne eliminée'], 200);
        }
    }

    /**
     * Méthode qui ajoute une personne
     * 
     * @OA\Parameter(
     *      name="nom",
     *      in="query",
     *      description="Nom de la personne",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="Prenom",
     *      in="query",
     *      description="Prenom de la personne",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="tel",
     *      in="query",
     *      description="Tel de la personne",
     *      @OA\Schema(type="int")
     * )
     * @OA\Parameter(
     *      name="email",
     *      in="query",
     *      description="email de la personne",
     *      @OA\Schema(type="varchar")
     * )
     * @OA\Tag(name="Personne")
     */
    #[Route('/insertPersonne', name: 'insertPersonne', methods: "POST")]
    public function insertPersonne(Request $request, PersonneRepository $repoPers, EntityManagerInterface $em): JsonResponse
    {
        $email = $request->query->get('email');
        $personne = $repoPers->findOneBy(['email' => $email]);

        if ($personne) {
            return new JsonResponse(['error' => 'Utilisateur connu'], 404);
        } else {

            $pers = new Personne;
            $pers->setPrenom($request->query->get('prenom'));
            $pers->setNom($request->query->get('nom'));
            $pers->setEmail($email);
            if (!empty($request->query->get('telephone')) && $request->query->get('telephone') != null) {
                $pers->setTel($request->query->get('telephone'));
            }

            if (!empty($request->query->get('possedeVoiture') && ($request->query->get('possedeVoiture') != null))) {
                if ($request->query->get('possedeVoiture') == 'true') {
                    $car = new Car;

                    $car->setConducteur($pers);
                    $car->setMatricule($request->query->get('plaque'));
                    $car->setColor($request->query->get('color'));
                    $car->setPlaces($request->query->get('places'));
                    $car->setModele($em->getRepository(Model::class)->findOneBy(['name' => strtoupper($request->query->get('model'))]));
                    $pers->addVoiture($car);
                    $em->persist($car);

                    $str = "car la";
                    var_dump($str);
                } else {
                    $str = "car pala";
                    var_dump($str);
                }
            } else {
                $str = "car pala";
                var_dump($str);
            }

            $userRepo = $em->getRepository(User::class);
            $user = $userRepo->findOneBy(['apiToken' => $request->headers->get('token')]);

            $pers->setIdUser($user);

            $em->persist($pers);

            $em->flush();

            return new JsonResponse(['message' => 'Personne ajoutée...'], 200);
        }
    }

    /**
     * Méthode qui modifie un inscrit
     * 
     * @OA\Parameter(
     *      name="prenom",
     *      in="query",
     *      description="Prenom de l'inscrit",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="tel",
     *      in="query",
     *      description="Numéro de téléphone de l'inscrit",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="login",
     *      in="query",
     *      description="Login de l'inscrit",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="password",
     *      in="query",
     *      description="Mot de passe de l'inscrit",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="Personne")
     */
    #[Route("/updatePersonne/{id}", name: "update", methods: "PUT")]
    public function update(int $id, Request $request, PersonneRepository $repoPers, EntityManagerInterface $em, UserPasswordHasherInterface $passEncoder): JsonResponse
    {



        $pers = $repoPers->find($id);

        if ($pers) {


            $user = $em->getRepository(User::class)->find($pers->getIdUser());

            if (!empty($request->query->get('nom')) && $request->query->get('nom') != null) {
                $pers->setNom($request->query->get('nom'));
            }
            if (!empty($request->query->get('prenom')) && $request->query->get('prenom') != null) {
                $pers->setPrenom($request->query->get('prenom'));
            }

            if (!empty($request->query->get('email')) && $request->query->get('email') != null) {
                $pers->setEmail($request->query->get('email'));
            }
            if (!empty($request->query->get('tel')) && $request->query->get('tel') != null) {
                $pers->setTel($request->query->get('tel'));
            }
            if (!empty($request->query->get('login')) && $request->query->get('login') != null) {
                $user->setUsername($request->query->get('login'));
            }
            if (!empty($request->query->get('pwd')) && $request->query->get('pwd') != null) {
                $hashedPassword = $passEncoder->hashPassword($user, $request->query->get('pwd'));
                $user->setPassword($hashedPassword);
            }


            $pers->setIdUser($user);

            $em->persist($pers);

            $em->flush();


            $em->flush();
            $resultat = ["Update faite!"];
        } else {
            $resultat = ["NOK" => "Personne non existante"];
        }




        return new JsonResponse($resultat);
    }
}
