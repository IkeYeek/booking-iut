<?php
namespace App\Controller;

use App\Entity\Configuration;
use App\Entity\Reservation;
use App\Entity\Seat;
use App\Entity\Show;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/{id}', name: 'app_show_book', methods: ['GET', 'POST'])]
    public function book(Request $request, Show $show, EntityManagerInterface $em) {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Vous devez être connecté');

        $configuration = $em->getRepository(Configuration::class)->findOneBy([]);

        // Get the Show entity
        $show = $em->getRepository(Show::class)->find($show->getId());
        $places = $em->getRepository(Reservation::class)->findByShow($show);
        if (!$show) {
            throw $this->createNotFoundException('The show does not exist');
        }
        $seatsRepo = $em->getRepository(Seat::class);
        $reservationRepo = $em->getRepository(Reservation::class);
        $places = $reservationRepo->findByShow($show);

        // Create a new Reservation entity
        $reservation = new Reservation();
        $reservation->setCorrespondingShow($show);
        $reservation->setUser($this->getUser());

        // Create the form
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        $maybeResa = $reservationRepo->findReservationFrom($this->getUser(), $show);
        if($maybeResa) {
           return  $this->redirectToRoute('reservation_edit', ['id' => $maybeResa->getId()]);
        }


        $nbRows = 8;
        $nbSeatsPerRows = 8;
        $function = new class($seatsRepo, $nbSeatsPerRows, $show) {
            private $seatsRepo;
            public function __construct($seatsRepo, $nbSeatsPerRows, $show)
            {
                $this->seatsRepo = $seatsRepo;
                $this->nbSeatsPerRows = $nbSeatsPerRows;
                $this->show = $show;
            }

            function getCellIndex($currentRow, $currentCol): string {
                $letter = chr(ord('A') + ($currentRow ) % $this->nbSeatsPerRows);
                $row = $currentCol + 1;
                return $letter . $row;
            }

            function isAvailableByName($name) {
                $seat = $this->seatsRepo->findOneByName($name);
                if ($seat === null) return false;
                return !$seat->hasAnyReservationFor($this->show);
            }

            function isAvailable($row, $seat) {
                $name = $this->getCellIndex($row, $seat);
                return $this->isAvailableByName($name);
            }
        };

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the selected seats
            $seats = $form->get('seats')->getData();
            $flagError = false;
            // Add the seats to the reservation
            foreach ($seats as $seat) {
                if ($function->isAvailableByName($seat->getName()))
                    $reservation->addSeat($seat);
                else {
                    $flagError = true;
                    $form->get("nom")->addError(new FormError("Vous essayez de réserver des sièges non disponibles ! Le siège " . $seat->getName() . " est déjà prit."));
                }
            }
            if (!$flagError) {  // Time really flies sorry for that awful pattern
                $reservation->setCreatedAt(new \DateTimeImmutable());

                $em->persist($reservation);
                $em->flush();

                $this->addFlash('success', 'Reservation created successfully');

                return $this->redirectToRoute('reservation_edit', [
                    'id' => $reservation->getId()
                ]);
            }
        }


        return $this->render('reservation/create.html.twig', [
            'form' => $form->createView(),
            'show' => $show,
            'placeName' => $configuration->getPlaceName(),
            'roomInfos' => [
                'rows' => $nbRows,
                'seatsPerRow' => $nbSeatsPerRows
            ],
            'functions' => $function,
            'places' => $places
        ]);
    }

    #[Route('/{id}/edit', name: 'reservation_edit', methods: ['GET', 'POST'])]
    public function show(Request $request, Reservation $reservation, EntityManagerInterface $em) {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Vous devez être connecté');
        $configuration = $em->getRepository(Configuration::class)->findOneBy([]);
        $reservationRepo = $em->getRepository(Reservation::class);
        $show = $reservation->getCorrespondingShow();
        $seats = $reservation->getSeats();
        if ($request->getMethod() == "POST") {
            $submittedToken = $request->request->get('token');
            if ($this->isCsrfTokenValid('delete-item', $submittedToken)) {
                $reservationRepo->remove($reservation, true);
            }
            return $this->render("reservation/delete_success.html.twig", [
                'placeName' => $configuration->getPlaceName(),
            ]);
        }
        return $this->render('reservation/delete.html.twig', [
            'placeName' => $configuration->getPlaceName(),
            'show' => $show,
            'places' => $seats
        ]);
    }
}