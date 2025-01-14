<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\User;
use App\Form\ArtistProfileType;
use App\Form\ArtistRegistrationType;
use App\Form\ArtistType;
use App\Repository\ArtistRepository;
use App\Repository\MusicalStyleRepository;
use App\Repository\UserRepository;
use App\Service\Locator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface as HasherUserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dj', name: 'app_')]
class DjController extends AbstractController
{
    #[Route('/', name: 'dj')]
    public function index(): Response
    {
        return $this->render('dj/index.html.twig');
    }

    #[Route('/inscription', name: 'registration')]
    public function register(
        HttpFoundationRequest $request,
        ArtistRepository $artistRepository,
        RequestStack $stack,
        ManagerRegistry $doctrine,
        UserRepository $userRepository,
        HasherUserPasswordHasherInterface $passwordHasher,
        Locator $locator,
        MusicalStyleRepository $musicalStyleRepo
    ): Response {
        $session = $stack->getSession();
        $artist = $session->get('artist') ?? new Artist();
        $registryManager = $doctrine->getManager();
        $user = new User();

        if ($session->get('step') === 3) {
            $form = $this->createForm(ArtistRegistrationType::class, $user);
        } elseif ($session->get('step') === 2) {
            $registryManager->persist($artist);
            $form = $this->createForm(ArtistProfileType::class, $artist);
        } else {
            $session->set('step', 1);
            $form = $this->createForm(ArtistType::class, $artist);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($session->get('step') === 1) {
                $this->addCoordinates($locator, $artist);
                $session->set('artist', $artist);
                $session->set('step', 2);
            } elseif ($session->get('step') === 2) {
                $session->set('step', 3);
            } elseif ($session->get('step') === 3) {
                foreach ($artist->getMusicalStyles() as $musicalStyle) {
                    $artist->removeMusicalStyle($musicalStyle);
                    $existingMusicalStyle = $musicalStyleRepo->find($musicalStyle->getId());
                    $artist->addMusicalStyle($existingMusicalStyle);
                }
                $session->remove('step');
                $session->remove('artist');
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $form['plainPassword']->getData()
                );
                $user->setPassword($hashedPassword);
                $userRepository->add($user, true);
                $artist->setUser($user);
                $artist->setEmail($user->getEmail());
                $artistRepository->add($artist, true);
                $this->addFlash('success', 'Votre demande a bien été transmise.');

                return $this->redirectToRoute('app_dj', [], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_registration', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm(
            'dj/registration/index.html.twig',
            [
                'form' => $form,
                'artist' => $artist,
            ]
        );
    }

    #[Route('/return', name: 'return')]
    public function return(RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        if ($session->get('step') > 1) {
            $session->set('step', $session->get('step') - 1);
        } else {
            $session->remove('step');
        }
        return $this->redirectToRoute('app_registration', [], Response::HTTP_SEE_OTHER);
    }

    private function addCoordinates(Locator $locator, Artist $artist): void
    {
        try {
            $locator->setCoordinates($artist);
        } catch (TransportException $te) {
            $this->addFlash('warning', 'Une erreur est survenue lors de la récupération de l\'adresse'
            . ', vous pouvez cependant poursuivre votre inscription.');
        } catch (Exception $e) {
            $this->addFlash('warning', $e->getMessage()
            . ', vous pouvez cependant poursuivre votre inscription.');
        }
    }
}
