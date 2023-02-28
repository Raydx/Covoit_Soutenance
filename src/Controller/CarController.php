<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\Car;
use App\Entity\Personne;
use App\Entity\Model;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class CarController extends AbstractController
{
    /**
     * Liste des voitures
     * 
     * @OA\Tag(name="Voiture")
     */
    #[Route('/listeVoiture', name: 'liste_Voiture', methods: "GET")]
    public function listeVoiture(CarRepository $doctrine, SerializerInterface $serializerInterface): JsonResponse
    {
        $liste = $doctrine->findAll();

        $array = array();
        foreach ($liste as $car) {
            $voiture = array([
                'id' => $car->getId(),
                'matricule' => $car->getMatricule(),
                'color' => $car->getColor(),
                'places' => $car->getPlaces(),
                'marque' => $car->getModele()->getName(),
                'Nom Conducteur' => $car->getConducteur()->getNom(),
                'Prenom Conducteur' => $car->getConducteur()->getPrenom(),
            ]);
            $array[] = $voiture;
        }
        $response = new JsonResponse($array);
        return $response;
    }
    /**
     * Méthode qui supprime une voiture
     * 
     * @OA\Parameter(
     *      name="id",
     *      in="query",
     *      description="id de la voiture concernée",
     *      @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Voiture")
     * 
     */
    #[Route('/deleteVoiture/{id}', name: 'delete_Voiture', methods: "DELETE")]
    public function deleteVoiture(int $id, CarRepository $doctrine, EntityManagerInterface $em): JsonResponse
    {
        $car = $doctrine->find($id);

        if (!$car) {
            return new JsonResponse(['error' => 'Voiture inconnu...'], 404);
        } else {

            $car->setConducteur(null);

            $em->remove($car);
            $em->flush();

            return new JsonResponse(['message' => 'Voiture eliminé'], 200);
        }
    }

    /**
     * Méthode qui ajoute une voiture
     * 
     * @OA\Parameter(
     *      name="matricule",
     *      in="query",
     *      description="plaque d'immatriculation de la voiture concernée",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="id_pers",
     *      in="query",
     *      description="id du propriétaire de la voiture",
     *      @OA\Schema(type="int")
     * )
     * 
     * @OA\Parameter(
     *      name="color",
     *      in="query",
     *      description="couleur de la voiture concernée",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="places",
     *      in="query",
     *      description="nombre de places disponibles au total dans la voiture",
     *      @OA\Schema(type="int")
     * )
     * 
     * @OA\Parameter(
     *      name="model",
     *      in="query",
     *      description="marque de la voiture",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="Voiture")
     * 
     */
    #[Route('/insertVoiture', name: 'insert_Voiture', methods: "POST")]
    public function insertVoiture(Request $request, EntityManagerInterface $em, CarRepository $doctrine): JsonResponse
    {

        $matricule = $request->query->get('matricule');
        $m = $doctrine->findOneBy(['matricule' => $matricule]);

        if ($m) {
            $resultat = ["NOK" => "Ca existe déjà celle là"];
            return new JsonResponse($resultat);
        } else {

            $car = new Car;
            $pers = $em->getRepository(Personne::class)->findOneBy(["id" => $request->query->get("id_pers")]);
            $car->setConducteur($pers);
            $car->setMatricule($matricule);
            $car->setColor($request->query->get('color'));
            $car->setPlaces($request->query->get('places'));
            $modele = $em->getRepository(Model::class)->findOneBy(['name' => strtoupper($request->query->get('model'))]);
            $car->setModele($modele);
            $pers->addVoiture($car);
            $em->persist($pers);
            $em->persist($car);

            $em->flush();
            $resultat = ["OK" => "Voiture ajoutée"];
            return new JsonResponse($resultat);
        }
    }
}
