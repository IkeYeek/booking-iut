<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Form\ConfigurationType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractController
{
    #[Route('/configuration/edit', name: 'app_configuration', methods: "GET")]
    public function edit(EntityManagerInterface $em)
    {
        $configuration = $em->getRepository(Configuration::class)->findOneBy([]);

        if (!$configuration) {
            throw $this->createNotFoundException('Aucune configuration n\'a été trouvée');
        }

        $form = $this->createForm(ConfigurationType::class, $configuration);

        return $this->render('configuration/edit.html.twig', [
            'form' => $form,
            'placeName' => $configuration->getPlaceName()
        ]);
    }

    #[Route('/configuration/edit', name: 'app_configuration_update', methods: "POST")]
    public function update(Request $request, EntityManagerInterface $em)
    {
        // Récupérer l'entité Configuration
        $configuration = $em->getRepository(Configuration::class)->findOneBy([]);

        // Créer le formulaire de modification et le lier à l'entité Configuration
        $form = $this->createForm(ConfigurationType::class, $configuration);
        $form->handleRequest($request);

        // Vérifier si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer les modifications en base de données
            $em->persist($configuration);
            $em->flush();

            // Rediriger vers la page d'accueil
            return $this->redirectToRoute('app_configuration_edit');
        }

        // Si le formulaire n'est pas valide, afficher à nouveau le formulaire de modification
        return $this->render('configuration/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
