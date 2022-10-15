<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Entity\Product;
use App\Entity\Review;
use App\Entity\User;
use App\Form\FilterType;
use App\Form\MailType;
use App\Repository\DestinationRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/search', name: 'app_search')]
    public function recherchePost(ManagerRegistry $doctrine, Request $request): Response
    {
        $key = $request->get('key');
        $target = $request->get('target');
        if($target == 'Destination'){
            $results = $doctrine->getRepository(Destination::class)->maRequete($key);
        }
        if($target == 'User'){
            $results = $doctrine->getRepository(User::class)->maRequete($key);
        }
        return $this->render('home/result.html.twig', [
            'controller_name' => 'HomeController',
            'key' => $key,
            'target' => $target,
            'results' => $results
        ]);
    }
    #[Route('/profile/{user}', name: 'app_profile')]
    public function profile(ManagerRegistry $doctrine, User $user): Response
    {
        $em = $doctrine->getRepository(User::class)->find($user);
        $reviews = $doctrine->getRepository(Review::class)->findBy(['user' => $user]);
        return $this->render('page-profile.html.twig',[
            'user' => $em,
            'reviews' => $reviews,
        ]);
    }


    #[Route('/contact', name: 'app_contact')]
    public function contact(MailerInterface $mailer, Request $request): Response
    {
        // ...

        $form = $this->createForm(MailType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()){
            $data = $form->getData();
            $mail = (new Email())
                ->from($data['email'])
                ->to(new Address('gurbetci.035@gmail.com', 'Suleyman'))
                ->subject('Email de contact')
                ->html('<p>' . $data['content'] . '</p>');

            $mailer->send($mail);
            $this->addFlash("success", "Votre message a bien été envoyée");
        }

        return $this->render('home/contact.html.twig',[
            'form' => $form->createView(),
        ]);

        // ...
    }
}
