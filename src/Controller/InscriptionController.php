<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\Trajet;
use App\Entity\Personne;
use App\Repository\TrajetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Statement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class InscriptionController extends AbstractController
{
    /**
     * Liste des réservations
     * 
     * @OA\Tag(name="Réservations")
     */
    #[Route('/listeInscription', name: 'liste_inscription', methods: "GET")]
    public function listeIncription(SerializerInterface $serializerInterface, EntityManagerInterface $em): JsonResponse
    {
        $connection = $em->getConnection();

        $sql = '
            SELECT * FROM personne_trajet
        ';
        $statement = $connection->prepare($sql);
        $resultSet = $statement->executeQuery();
        $passagers = $resultSet->fetchAllAssociative();

        $listeJson = $serializerInterface->serialize($passagers, 'json');

        return new JsonResponse($listeJson, 200, [], true);
    }
    /**
     * Méthode qui affiche un trajet
     * 
     * @OA\Parameter(
     *      name="id_trajet",
     *      in="query",
     *      description="Id du trajet concerné",
     *      @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Réservations")
     */
    #[Route('/listeInscriptionConducteur', name: 'listeInscriptionConducteur', methods: "GET")]
    public function listeIncriptionConducteur(Request $request, SerializerInterface $serializerInterface, EntityManagerInterface $em): JsonResponse
    {
        $idtrajet = $request->query->get('id_trajet');
        $trajet = $em->getRepository(Trajet::class)->findOneBy(["id" => $idtrajet]);

        if (!$trajet) {
            return new JsonResponse(['error' => 'Trajets inconnu...'], 404);
        } else {

            $trajetJson = new JsonResponse();

            $trajetJson->setData([
                'id' => $trajet->getId(),
                'date' => $trajet->getDate()->format('d-m-Y'),
                'heureDepart' => $trajet->getStartHour()->format('H:i'),
                'heureArrivee' => $trajet->getArrivalHour()->format('H:i'),
                'Kms' => $trajet->getKms(),
                'places' => $trajet->getAvailablePlaces(),
                'conducteurNom' => $trajet->getConducteur()->getNom(),
                'conducteurPrenom' => $trajet->getConducteur()->getPrenom()
            ]);

            $passagers = $trajet->getPassagers();

            $array = array();
            foreach ($passagers as $passager) {
                $personne = array([
                    'id' => $passager->getId(),
                    'passagerNom' => $passager->getNom(),
                    'passagerPrenom' => $passager->getPrenom()
                ]);
                $array[] = $personne;
            }

            $contenu1 = json_decode($trajetJson->getContent(), true);

            $fusion = array_merge($contenu1, $array);

            $reponse = new JsonResponse($fusion);

            return $reponse;
        }
    }
    /**
     * Méthode qui affiche une réservation
     * 
     * @OA\Parameter(
     *      name="id_pers",
     *      in="query",
     *      description="Id du passager",
     *      @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Réservations")
     */
    #[Route('/listeInscriptionUser', name: 'listeInscriptionUser', methods: "GET")]
    public function listeIncriptionUser(Request $request, SerializerInterface $serializerInterface, EntityManagerInterface $em): JsonResponse
    {
        $idpersonne = $request->query->get('id_pers');
        $personne = $em->getRepository(Personne::class)->findOneBy(["id" => $idpersonne]);

        if (!$personne) {
            return new JsonResponse(['error' => 'Trajets inconnu...'], 404);
        } else {


            $trajetsRev = $personne->getTrajetsReserves();

            $array = array();
            foreach ($trajetsRev as $trajet) {
                $ride = array([
                    'id' => $trajet->getId(),
                    'date' => $trajet->getDate()->format('d-m-Y'),
                    'heureDepart' => $trajet->getStartHour()->format('H:i'),
                    'heureArrivee' => $trajet->getArrivalHour()->format('H:i'),
                    'Kms' => $trajet->getKms(),
                    'places' => $trajet->getAvailablePlaces(),
                    'conducteurNom' => $trajet->getConducteur()->getNom(),
                    'conducteurPrenom' => $trajet->getConducteur()->getPrenom()
                ]);
                $array[] = $ride;
            }



            $reponse = new JsonResponse($array);

            return $reponse;
        }
    }
    /**
     * Méthode qui inscrit un passager à un trajet
     * 
     * @OA\Parameter(
     *      name="id_pers",
     *      in="query",
     *      description="Id du passager concerné",
     *      @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *      name="id_trajet",
     *      in="query",
     *      description="Id du trajet concerné",
     *      @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Réservations")
     */
    #[Route('/insertInscription', name: 'insertInscription', methods: "POST")]
    public function insertIncription(Request $request, SerializerInterface $serializerInterface, EntityManagerInterface $em): JsonResponse
    {
        $idpers = $request->query->get('id_pers');
        $idtrajet = $request->query->get('id_trajet');

        $pers = $em->getRepository(Personne::class)->findOneBy(["id" => $idpers]);
        $trajet = $em->getRepository(Trajet::class)->findOneBy(["id" => $idtrajet]);

        if ($trajet->getAvailablePlaces() >= $trajet->getPassagers()->count()) {
            if ($trajet->getConducteur() == $pers) {
                $resultat = ["NOK" => "Vous ne pouvez pas conduire ET être passager !"];
            } else {

                $pers->addTrajetsReserf($trajet);
                $trajet->setAvailablePlaces($trajet->getAvailablePlaces() - 1);
                $em->persist($trajet);
                $em->persist($pers);
                $em->flush();
                $resultat = ["OK" => "Vous avez été inscrit. Bon voyage ! ;)"];
            }
        } else {
            $resultat = ["NOK" => "Voiture complète pour ce trajet ... :("];
        }

        return new JsonResponse($resultat);
    }

    /**
     * Méthode qui supprime une réservation
     * 
     * @OA\Parameter(
     *      name="id_pers",
     *      in="query",
     *      description="id du passager concerné",
     *      @OA\Schema(type="int")
     * )
     * 
     * @OA\Parameter(
     *      name="id_trajet",
     *      in="query",
     *      description="id du trajet concerné",
     *      @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Réservations")
     * 
     */
    #[Route('/deleteInscription', name: 'deleteInscription', methods: "DELETE")]
    public function deleteIncription(Request $request, SerializerInterface $serializerInterface, EntityManagerInterface $em): JsonResponse
    {
        $idpers = $request->query->get('id_pers');
        $idtrajet = $request->query->get('id_trajet');

        $pers = $em->getRepository(Personne::class)->findOneBy(["id" => $idpers]);
        $trajet = $em->getRepository(Trajet::class)->findOneBy(["id" => $idtrajet]);

        $pers->removeTrajetsReserf($trajet);

        $trajet->setAvailablePlaces($trajet->getAvailablePlaces() + 1);

        $em->persist($trajet);
        $em->persist($pers);
        $em->flush();

        return new JsonResponse(["OK" => "Vous avez été désinscrit."]);
    }
}
