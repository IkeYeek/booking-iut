<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Entity\Reservation;
use App\Entity\Seat;
use App\Entity\Show;
use App\Form\ReservationType;
use App\Form\ShowType;
use App\Repository\ShowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/show')]
class ShowController extends AbstractController
{
    #[Route('/', name: 'app_show_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, ShowRepository $showRepository, PaginatorInterface $paginator): Response
    {
        $configuration = $em->getRepository(Configuration::class)->findOneBy([]);
        $shows = $showRepository->findAllUpcomingShowsQuery();
        $pagination = $paginator->paginate(
            $shows, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            4 /*limit per page*/
        );
        return $this->render('show/index.html.twig', [
            'pagination' => $pagination,
            'placeName' => $configuration->getPlaceName(),
            'totalPlaces' => $configuration->getNbSeats()
        ]);
    }

    #[Route('/{id}/map', name: 'app_show_map', methods: ['GET'])]
    public function map(EntityManagerInterface $em, Show $show)
    {
        $configuration = $em->getRepository(Configuration::class)->findOneBy([]);
        $seatsRepo = $em->getRepository(Seat::class);
        $functions = new class($seatsRepo, $configuration->getNbSeatsPerRow(), $show) {
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
        return $this->render('show/map.html.twig', ['placeName' => $configuration->getPlaceName(), 'roomInfos' => [
            'rows' => $configuration->getNbRows(),
            'seatsPerRow' => $configuration->getNbSeatsPerRow(),

        ], 'functions' => $functions,]);
    }

    #[Route('/new', name: 'app_show_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ShowRepository $showRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Vous devez être administrateur pour accéder à cette page.');

        $configuration = $em->getRepository(Configuration::class)->findOneBy([]);
        $show = new Show();
        $form = $this->createForm(ShowType::class, $show);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($showRepository->findShowOverlappingWith($form->getData()->getDateStart(), $form->getData()->getDateEnd())) {
                $form->get("date_start")->addError(new FormError('Ces dates sont déjà prises !'));
                $form->get("date_end")->addError(new FormError('Ces dates sont déjà prises !'));
                return $this->render('show/new.html.twig', [
                    'show' => $show,
                    'form' => $form->createView(),
                    'placeName' => $configuration->getPlaceName()
                ]);
            }
            $showRepository->save($show, true);

            return $this->redirectToRoute('app_show_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('show/new.html.twig', [
            'show' => $show,
            'form' => $form,
            'placeName' => $configuration->getPlaceName()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_show_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Show $show, ShowRepository $showRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Vous devez être administrateur pour accéder à cette page.');

        $configuration = $em->getRepository(Configuration::class)->findOneBy([]);
        $form = $this->createForm(ShowType::class, $show);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $showRepository->save($show, true);

            return $this->redirectToRoute('app_show_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('show/edit.html.twig', [
            'show' => $show,
            'form' => $form,
            'placeName' => $configuration->getPlaceName()
        ]);
    }

    #[Route('/{id}', name: 'app_show_delete', methods: ['POST'])]
    public function delete(Request $request, Show $show, ShowRepository $showRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Vous devez être administrateur pour accéder à cette page.');

        if ($this->isCsrfTokenValid('delete'.$show->getId(), $request->request->get('_token'))) {
            $showRepository->remove($show, true);
        }

        return $this->redirectToRoute('app_show_index', [], Response::HTTP_SEE_OTHER);
    }
}
