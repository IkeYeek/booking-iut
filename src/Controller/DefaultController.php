<?php

namespace App\Controller;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $em): Response
    {
        $configuration = $em->getRepository(Configuration::class)->findOneBy([]);

        return $this->render('default/index.html.twig', [
            'placeName' => $configuration->getPlaceName(),
            'placeAddress' => $configuration->getPlaceAddress(),
            'iframeUrl' => 'https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d2794.035620333259!2d' . $configuration->getLatitude() . '!3d' . $configuration->getLongitude() . '!2m3!1f0!2f0!3f0!3m2!1i1044!2i768!4f13.1!3m2!1m1!2zNFCsXZiJzU4LjYiTiAwwrAzOSc1OS4yIkU!5e0'
        ]);
    }
}
