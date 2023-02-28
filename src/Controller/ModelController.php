<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\Model;
use App\Repository\ModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ModelController extends AbstractController
{
    /**
     * Liste des marques de voiture
     * 
     * @OA\Parameter(
     *      name="name",
     *      in="query",
     *      description="Marque de la voiture",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="Marques")
     */
    #[Route('/listeModeles', name: 'liste_Modeles', methods: "GET")]
    public function listeModeles(ModelRepository $doctrine, SerializerInterface $serializerInterface): JsonResponse
    {
        $liste = $doctrine->findAll();
        $listeJson = $serializerInterface->serialize($liste, 'json');

        return new JsonResponse($listeJson, 200, [], true);
    }

    /**
     *  Méthode qui supprime une marque de voiture
     * 
     * @OA\Parameter(
     *      name="name",
     *      in="query",
     *      description="Supprime une marque de voiture",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="Marques")
     */
    #[Route('/deleteModele{id}', name: 'delete_Model', methods: "DELETE")]
    public function deleteModele(int $id, ModelRepository $doctrine, EntityManagerInterface $em): JsonResponse
    {
        $model = $doctrine->find($id);

        if (!$model) {
            return new JsonResponse(['error' => 'Modèle inconnu'], 404);
        } else {
            $em->remove($model);
            $em->flush();

            return new JsonResponse(['message' => 'Le modèle à été supprimé'], 200);
        }
    }

    /**
     * Méthode qui ajoute une marque de voiture
     * 
     * @OA\Parameter(
     *      name="name",
     *      in="query",
     *      description="Ajoute une marque de voiture",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="Marques")
     */
    #[Route('/insertModele', name: 'insert_modele', methods: "POST")]
    public function insertModele(Request $request, EntityManagerInterface $em, ModelRepository $doctrine): JsonResponse
    {

        $nom = strtoupper($request->query->get('name'));
        $m = $doctrine->findOneBy(['name' => $nom]);

        if ($m) {
            $resultat = ["NOK" => "Ce nom existe déjà"];
            return new JsonResponse($resultat);
        } else {

            $model = new Model;
            $model->setName($nom);

            $em->persist($model);

            $em->flush();
            $resultat = ["OK"];
            return new JsonResponse($resultat);
        }
    }
}
