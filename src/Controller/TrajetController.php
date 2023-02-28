<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\Personne;
use App\Entity\Trajet;
use App\Entity\User;
use App\Entity\City;
use App\Repository\TrajetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;


class TrajetController extends AbstractController
{
    /**
     * Liste des trajets
     * 
     * @OA\Parameter(
     *      name="liste_trajets",
     *      in="query",
     *      description="Liste de tous les trajets",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="Trajets")
     */
    #[Route('/listeTrajet', name: 'liste_trajet', methods: "GET")]
    public function listeTrajet(TrajetRepository $doctrine, SerializerInterface $serializerInterface): JsonResponse
    {
        $liste = $doctrine->findAll();
        $listeJson = $serializerInterface->serialize($liste, 'json');

        return new JsonResponse($listeJson, 200, [], true);
    }

    /**
     * Méthode qui supprime un trajet
     * 
     * @OA\Parameter(
     *      name="Trajet",
     *      in="query",
     *      description="Nom du trajet à supprimer",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Tag(name="Trajets")
     */
    #[Route('/deleteTrajet/{id}', name: 'delete_trajet', methods: "DELETE")]
    public function deleteTrajet(int $id, TrajetRepository $doctrine, EntityManagerInterface $em): JsonResponse
    {
        $trajet = $doctrine->find($id);

        if (!$trajet) {
            return new JsonResponse(['error' => 'Aucun trajet ne correspond à cet id'], 404);
        } else {

            foreach ($trajet->getPassagers() as $personne) {
                $personne->removeTrajetsReserf($trajet);
            }
            $trajet->setConducteur(null);
            $trajet->setVoiture(null);
            $trajet->setArrivalCity(null);
            $trajet->setStartCity(null);
            $em->remove($trajet);
            $em->flush();

            return new JsonResponse(['message' => 'Trajet supprimé'], 200);
        }
    }

    /**
     * Méthode qui recherche un trajet
     * 
     * @OA\Parameter(
     *      name="ville_depart",
     *      in="query",
     *      description="Ville de départ du trajet",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="ville_arrivee",
     *      in="query",
     *      description="Ville d'arrivée du trajet",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="date",
     *      in="query",
     *      description="Date de départ du trajet",
     *      @OA\Schema(type="date")
     * )
     * 
     * @OA\Tag(name="Trajets")
     */
    #[Route('/rechercheTrajet', name: 'recherche_trajet', methods: "GET")]
    public function rechercheTrajet(Request $request, EntityManagerInterface $em, TrajetRepository $doctrine, SerializerInterface $serializerInterface): JsonResponse
    {

        $cherche = ['-', "'", 'é', 'è', 'ê', 'à', 'É', 'û', 'ô', 'ö', 'ò', 'ù', 'ÿ', 'Ö', 'á', 'í', 'ú', 'î', 'Å', 'ë', 'À', 'Á', 'Â', 'Ã', 'Å', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'È', 'Ê', 'Ì', 'Í', 'Î', 'Ï'];
        $remplace = [' ', " ", 'e', 'e', 'e', 'a', 'E', 'u', 'o', 'o', 'o', 'u', 'y', 'O', 'a', 'i', 'u', 'i', 'a', 'e', 'a', 'a', 'a', 'a', 'a', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'e', 'e', 'i', 'i', 'i', 'i'];
        $villeD = strtolower(str_replace($cherche, $remplace, $request->query->get('ville_depart')));
        $villeA = strtolower(str_replace($remplace, $remplace, $request->query->get('ville_arrivee')));
        $depart = $em->getRepository(City::class)->findOneBy(['name' => $villeD]);
        $arrivee = $em->getRepository(City::class)->findOneBy(['name' => $villeA]);

        $datetime = new \DateTime($request->query->get('date'));



        $trajets = $doctrine->findBy((['start_city' => $depart, 'arrival_city' => $arrivee, 'date' => $datetime]));
        if (!$trajets) {
            return new JsonResponse(['error' => 'Ce trajet n\'est pas disponible'], 404);
        } else {

            $response = new JsonResponse();
            $nb = 0;
            $array = array();
            foreach ($trajets as $trajet) {
                $nb++;
                $ride = array([
                    'id' => $trajet->getId(),
                    'Kms' => $trajet->getKms(),
                    'date' => $trajet->getDate()->format('d-m-Y'),
                    'heureDepart' => $trajet->getStartHour()->format('H:i'),
                    'heureArrivee' => $trajet->getArrivalHour()->format('H:i'),
                    'places' => $trajet->getAvailablePlaces(),
                    'conducteurNom' => $trajet->getConducteur()->getNom(),
                    'conducteurPrenom' => $trajet->getConducteur()->getPrenom()
                ]);

                //$contenu2 = json_decode($ride->getContent(), true);

                $array[] = $ride;
            }

            $response = new JsonResponse($array);
            return $response;
        }
    }

    /**
     * Méthode qui ajoute un trajet
     * 
     * @OA\Parameter(
     *      name="ville_depart",
     *      in="query",
     *      description="Ville de départ du trajet",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="ville_arrivee",
     *      in="query",
     *      description="Ville d'arrivée du trajet",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="date",
     *      in="query",
     *      description="Date de départ du trajet",
     *      @OA\Schema(type="date")
     * )
     * 
     * @OA\Parameter(
     *      name="heure_depart",
     *      in="query",
     *      description="Heure de départ du trajet",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="heure_arrivee",
     *      in="query",
     *      description="Heure d'arrivée du trajet",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="kms",
     *      in="query",
     *      description="Nombre de kilomètres total",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="id_pers",
     *      in="query",
     *      description="Id du conducteur",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="places",
     *      in="query",
     *      description="Nombre de places disponibles dans la voiture",
     *      @OA\Schema(type="int")
     * )
     * 
     * @OA\Tag(name="Trajets")
     */
    #[Route('/insertTrajet', name: 'insert_trajet', methods: "POST")]
    public function insertTrajet(Request $request, EntityManagerInterface $em): JsonResponse
    {

        if ($request->isMethod("post")) {



            //Recuperer les données passées dans le query

            $cherche = ['-', "'", 'é', 'è', 'ê', 'à', 'É', 'û', 'ô', 'ö', 'ò', 'ù', 'ÿ', 'Ö', 'á', 'í', 'ú', 'î', 'Å', 'ë', 'À', 'Á', 'Â', 'Ã', 'Å', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'È', 'Ê', 'Ì', 'Í', 'Î', 'Ï'];
            $remplace = [' ', " ", 'e', 'e', 'e', 'a', 'E', 'u', 'o', 'o', 'o', 'u', 'y', 'O', 'a', 'i', 'u', 'i', 'a', 'e', 'a', 'a', 'a', 'a', 'a', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'e', 'e', 'i', 'i', 'i', 'i'];
            $villeD = strtolower(str_replace($cherche, $remplace, $request->query->get('ville_depart')));
            $villeA = strtolower(str_replace($remplace, $remplace, $request->query->get('ville_arrivee')));
            $datetime = new \DateTime($request->query->get('date'));
            $heureD = new \DateTime($request->query->get('heureDepart'));
            $heureA = new \DateTime($request->query->get('heureArrivee'));
            $id = $request->query->get('id_pers');
            //Et ensuite chercher les entites necessaire pour
            $depart = $em->getRepository(City::class)->findOneBy(['name' => $villeD]);
            $arrivee = $em->getRepository(City::class)->findOneBy(['name' => $villeA]);
            $personne = $em->getRepository(Personne::class)->findOneBy(['id' => $id]);
            //Pour ensuite créer le trajet à ajouter
            $trajet = new Trajet;
            $trajet->setKms($request->query->get('kms'));
            $trajet->setStartCity($depart);
            $trajet->setArrivalCity($arrivee);
            $trajet->setConducteur($personne);
            $trajet->setVoiture($personne->getVoiture()[0]);
            //Faire attention aux nombre de places par rapport à la voiture!
            if ($request->query->get('places') > $personne->getVoiture()[0]->getPlaces()) {
                return new JsonResponse(['error' => 'Nombre de places accordée supérieure au nombre de places dispo...'], 404);
            } else {
                $trajet->setAvailablePlaces($request->query->get('places'));
            }
            $trajet->setStartHour($heureD);
            $trajet->setArrivalHour($heureA);
            $trajet->setDate($datetime);

            $em->persist($trajet);

            $em->flush();

            $resultat = ["OK"];
        } else {
            $resultat = ["NOK: Il faut utiliser la methode POST"];
        }

        return new JsonResponse($resultat);
    }
}
