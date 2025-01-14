<?php

namespace App\Controller;

use App\Config\ReservationStatus;
use App\Entity\Reservation;
use App\Form\ReservationClientInfosType;
use App\Form\ReservationEventInfosType;
use App\Repository\ReservationRepository;
use App\Service\Locator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/client', name: 'client_')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function reservation(
        Request $request,
        RequestStack $requestStack,
        ReservationRepository $reservationRepo,
        MailerInterface $mailer,
        Locator $locator
    ): Response {
        $session = $requestStack->getSession();
        $reservation = $session->get('reservationForm') ?? new Reservation();

        $step = false;
        if ($session->has('isReservationClientInfosValid') && $session->get('isReservationClientInfosValid')) {
            $step = true;
            $form = $this->createForm(ReservationEventInfosType::class, $reservation);
        } else {
            $form = $this->createForm(ReservationClientInfosType::class, $reservation);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$step) {
                $session->set('reservationForm', $reservation);
                $session->set('isReservationClientInfosValid', true);
            } else {
                $session->remove('reservationForm');
                $session->remove('isReservationClientInfosValid');
                $reservation->setStatus(ReservationStatus::Waiting->name);
                try {
                    $locator->setCoordinates($reservation);
                    $this->addFlash('success', 'Votre demande a bien été transmise.');
                } catch (TransportException $te) {
                    $this->addFlash('warning', 'Une erreur est survenue lors de la récupération de l\'adresse'
                    . ' mais votre demande a bien été transmise.');
                } catch (Exception $e) {
                    $this->addFlash('warning', $e->getMessage()
                    . ' mais votre demande a bien été transmise.');
                }
                $reservationRepo->add($reservation, true);
                $this->sendReservationMail($reservation, $mailer);

                return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
            }
            return $this->redirectToRoute('client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/index.html.twig', [
            'form' => $form,
            'step' => $step
        ]);
    }

    #[Route('/back', name: 'back')]
    public function backForm(RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        if ($session->has('isReservationClientInfosValid')) {
            $session->remove('isReservationClientInfosValid');
        }
        return $this->redirectToRoute('client_index', [], Response::HTTP_SEE_OTHER);
    }

    private function sendReservationMail(Reservation $reservation, MailerInterface $mailer): void
    {
        $email = (new Email())
                ->from($reservation->getEmail())
                ->to($this->getParameter('mailer_from'))
                ->subject('Une nouvelle demande de réservation')
                ->html($this->renderView('client/notification_email_reservation.html.twig', [
                    'reservation' => $reservation
                ]));

        $mailer->send($email);
    }
}
