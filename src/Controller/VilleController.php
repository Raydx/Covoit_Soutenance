<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\City;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class VilleController extends AbstractController
{
    /**
     * Liste des villes
     * 
     * @OA\Tag(name="Villes")
     */
    #[Route('/listeVilles', name: 'liste_ville', methods: "GET")]
    public function listeVilles(CityRepository $doctrine, SerializerInterface $serializerInterface): JsonResponse
    {
        $liste = $doctrine->findAll();
        $listeJson = $serializerInterface->serialize($liste, 'json');

        return new JsonResponse($listeJson, 200, [], true);
    }

    /**
     * Méthode qui supprime une ville
     * 
     * @OA\Parameter(
     *      name="Nom",
     *      in="query",
     *      description="Nom de la ville à supprimer",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="Villes")
     * 
     */
    #[Route('/deleteVille/{id}', name: 'delete_Ville', methods: "DELETE")]
    public function deleteVille(int $id, CityRepository $doctrine, EntityManagerInterface $em): JsonResponse
    {
        $Ville = $doctrine->find($id);

        if (!$Ville) {
            return new JsonResponse(['error' => 'Ville inconnu...'], 404);
        } else {
            $em->remove($Ville);
            $em->flush();

            return new JsonResponse(['message' => 'Ville eliminé'], 200);
        }
    }
    /**
     * Méthode qui ajoute une ville
     * 
     * @OA\Parameter(
     *      name="Nom",
     *      in="query",
     *      description="Nom de la ville à ajouter",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="Villes")
     * 
     */
    #[Route('/insertVille', name: 'insert_ville', methods: "POST")]
    public function insertVilles(Request $request, EntityManagerInterface $em, CityRepository $doctrine): JsonResponse
    {

        $cherche = ['-', "'", 'é', 'è', 'ê', 'à', 'É', 'û', 'ô', 'ö', 'ò', 'ù', 'ÿ', 'Ö', 'á', 'í', 'ú', 'î', 'Å', 'ë', 'À', 'Á', 'Â', 'Ã', 'Å', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'È', 'Ê', 'Ì', 'Í', 'Î', 'Ï'];
        $remplace = [' ', " ", 'e', 'e', 'e', 'a', 'E', 'u', 'o', 'o', 'o', 'u', 'y', 'O', 'a', 'i', 'u', 'i', 'a', 'e', 'a', 'a', 'a', 'a', 'a', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'e', 'e', 'i', 'i', 'i', 'i'];
        $nom = strtolower(str_replace($cherche, $remplace, $request->query->get('nom')));
        $v = $doctrine->findOneBy(['name' => $nom]);

        if ($v) {
            $resultat = ["NOK"];
            return new JsonResponse($resultat);
        } else {

            $ville = new City;
            $ville->setName($nom);
            $ville->setPostalCode($request->query->get('cp'));

            $minlang = 42.0;
            $maxlang = 48.0;
            $rangelang = $maxlang - $minlang;
            $lattitude = ($rangelang * rand() / getrandmax()) + $minlang;

            $minlong = 0.0;
            $maxlong = 5.0;
            $rangelong = $maxlong - $minlong;
            $longitude = ($rangelong * rand() / getrandmax()) + $minlong;

            $ville->setLatitude($lattitude);
            $ville->setLongitude($longitude);

            $em->persist($ville);

            $em->flush();
            $resultat = ["OK"];
            return new JsonResponse($resultat);
        }
    }
}
