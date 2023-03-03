<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Entity\Seat;
use App\Form\GenerateSeatsFormType;
use App\Form\SeatType;
use App\Repository\SeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/seat')]
class SeatController extends AbstractController
{
    #[Route('/', name: 'app_seat_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        function getCellIndex(int $cols, int $currentRow, int $currentCol): string {
            $letter = chr(ord('A') + ($currentCol ) % $cols);
            $row = $currentRow + 1;
            return $letter . $row;
        }

        $configuration = $em->getRepository(Configuration::class)->findOneBy([]);
        $form = $this->createForm(GenerateSeatsFormType::class, [
            'rows' => 8,
            'seat_per_rows' => 8
        ]);
        $form->handleRequest($request);
        $curr = "A1";
        if ($form->isSubmitted() && $form->isValid()) {
            $repo = $em->getRepository(Seat::class);
            $data = $form->getData();
            for ($i = 0; $i < $data["rows"]; $i += 1) {
                for ($j = 0; $j < $data["seat_per_rows"]; $j += 1) {
                    $seatName = getCellIndex($data["seat_per_rows"], $i, $j) . "\n";
                    $seat = new Seat();
                    $seat->setName($seatName);
                    if ($repo->findOneByName($seatName) === null) {
                        $repo->save($seat);
                    }
                }
            }
            $em->flush();
        }


        return $this->render('seat/index.html.twig', [
            'form' => $form->createView(),
            'placeName' => $configuration->getPlaceName()
        ]);
    }
}
