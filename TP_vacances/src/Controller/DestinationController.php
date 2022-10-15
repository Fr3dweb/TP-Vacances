<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Entity\Review;
use App\Entity\Type;
use App\Entity\User;
use App\Form\DestinationType;
use App\Form\ReviewType;
use App\Form\TypeType;
use App\Repository\DestinationRepository;
use App\Repository\ReviewRepository;
use App\Repository\TypeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/destination')]
class DestinationController extends AbstractController
{
    #[Route('/', name: 'app_destination_index', methods: ['GET'])]
    public function index(DestinationRepository $destinationRepository,Request $request, PaginatorInterface $paginator, TypeRepository $typeRepository): Response
    {
        $this->destinationRepository = $destinationRepository;
        $text = '';
        $filter = $request->get('filter');
        $pays = $request->get('pays');
        $filterList = $destinationRepository->findBy(['Type' => $filter, 'country' => $pays]);
        if ($pays == '-- Tous les pays --'){
            $pagination = $paginator->paginate(
                $this->destinationRepository->findBy(['Type' => $filter]),
                $request->query->getInt('page', 1)/*page number*/,
                8/*limit per page*/
            );
        }
        else if($filterList){
                $pagination = $paginator->paginate(
                    $this->destinationRepository->findBy(['Type' => $filter, 'country' => $pays]),
                    $request->query->getInt('page', 1)/*page number*/,
                    8/*limit per page*/
                );
        }
        else{
            if ($pays != null){
                $text = 'Aucun resultat trouver avec votre filtre';
            }
            $pagination = $paginator->paginate(
                $this->destinationRepository->findAll(),
                $request->query->getInt('page', 1)/*page number*/,
                8/*limit per page*/
            );
        }

        $typeList = $typeRepository->findAll();
        return $this->render('destination/index.html.twig', [
            'destination' => $this->destinationRepository->findAll(),
            'pagination' => $pagination,
            'typeList' => $typeList,
            'text' => $text,
        ]);
    }


    #[Route('/new', name: 'app_destination_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DestinationRepository $destinationRepository): Response
    {
        $destination = new Destination();
        $form = $this->createForm(DestinationType::class, $destination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $destinationRepository->save($destination, true);

            return $this->redirectToRoute('app_destination_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('destination/new.html.twig', [
            'destination' => $destination,
            'form' => $form,
        ]);
    }

    #[Route('/{destination}', name: 'app_destination_show', methods: ['GET', 'POST'])]
    public function show(ManagerRegistry $doctrine, Request $request, ReviewRepository $reviewRepository, Destination $destination): Response
    {
        $review = new Review();
        $review->setUser($this->getUser());
        $review->setDestination($destination);
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);
        $em = $doctrine->getManager();
        $userList = $em->getRepository(User::class)->findAll();
        $reviewList = $em->getRepository(Review::class)->findAll();
        $typeList = $em->getRepository(Type::class)->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $reviewRepository->save($review, true);
            return $this->redirect($request->getUri());
        }

        return $this->renderForm('destination/show.html.twig', [
            'destination' => $destination,
            'form' => $form,
            'userList' => $userList,
            'reviewList' => $reviewList,
            'typeList' => $typeList,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_destination_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Destination $destination, DestinationRepository $destinationRepository): Response
    {
        $form = $this->createForm(DestinationType::class, $destination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $destinationRepository->save($destination, true);

            return $this->redirectToRoute('app_destination_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('destination/edit.html.twig', [
            'destination' => $destination,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_destination_delete', methods: ['POST'])]
    public function delete(Request $request, Destination $destination, DestinationRepository $destinationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $destination->getId(), $request->request->get('_token'))) {
            $destinationRepository->remove($destination, true);
        }

        return $this->redirectToRoute('app_destination_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/', name: 'app_filter', methods: ['GET'])]
    public function filter(DestinationRepository $destinationRepository, TypeRepository $typeRepository, string $filter, Request $request): Response
    {
        $filter = $request->get('filter');
        return $this->render('destination/index.html.twig', [
            'destinations' => $destinationRepository->findAll(),
            'typeList' => $typeRepository->findAll(),
            'filter' => $filter,
        ]);
    }
}
