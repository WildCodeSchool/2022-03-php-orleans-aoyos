<?php

namespace App\Controller;

use App\Form\ArtistEditType;
use App\Form\DocumentType;
use App\Repository\ArtistRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/espace-dj', name: 'dashboard_dj_')]
class DjProfileController extends AbstractController
{
    #[Route('/profil', name: 'profile', methods: ['GET', 'POST'])]
    public function index(
        ArtistRepository $artistRepository,
        Request $request,
        AuthenticationUtils $authenticationUtils
    ): Response {
        $emailArtist = $authenticationUtils->getLastUsername();
        $artist = $artistRepository->findOneBy(['email' => $emailArtist]);

        $form = $this->createForm(ArtistEditType::class, $artist);
        $form->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid())) {
            $artistRepository->add($artist, true);

            $this->addFlash('success', 'Votre profil a bien été modifié.');

            return $this->redirectToRoute('dashboard_dj_profile', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('dj_dashboard/profile/edit.html.twig', [
            'artist' => $artist,
            'form' => $form,
        ]);
    }

    #[Route('/documents', name: 'documents', methods: ['GET', 'POST'])]
    public function documents(
        ArtistRepository $artistRepository,
        Request $request,
        AuthenticationUtils $authenticationUtils
    ): Response {
        $emailArtist = $authenticationUtils->getLastUsername();
        $artist = $artistRepository->findOneBy(['email' => $emailArtist]);
        $documents = $artist->getDocuments();

        $form = $this->createForm(DocumentType::class, $documents);
        $form->handleRequest($request);

        return $this->renderForm('dj_dashboard/profile/edit.html.twig', [
            'artist' => $artist,
            'form' => $form,
        ]);
    }
}
