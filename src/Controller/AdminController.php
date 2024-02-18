<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Entity\Post;
use App\Form\Groupe1Type;
use App\Form\PostType;
use App\Repository\GroupeRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    #[Route('/admin/groupe', name: 'app_groupe_admin', methods: ['GET'])]
    public function admin_see_groupe(GroupeRepository $groupeRepository): Response
    {
        return $this->render('admin/groupe.html.twig', [
            'groupes' => $groupeRepository->findAll(),
        ]);
    }

    #[Route('/admin/{id}', name: 'app_groupe_showadmin', methods: ['GET'])]
    public function show(Groupe $groupe): Response
    {
        return $this->render('admin/showgroupe.html.twig', [
            'groupe' => $groupe,
        ]);
    }


    #[Route('/aaaa', name: 'app_groupeadmin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $groupe = new Groupe();
        $form = $this->createForm(Groupe1Type::class, $groupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $groupe->setImage($newFilename);
            }

            $entityManager->persist($groupe);
            $entityManager->flush();

            return $this->redirectToRoute('app_groupe_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/addgroupe.html.twig', [
            'groupe' => $groupe,
            'form' => $form,
        ]);
    }

    #[Route('/adminn/posts', name: 'app_post_admin', methods: ['GET'])]
    public function seepostsadmin(PostRepository $postRepository): Response
    {
        return $this->render('admin/postadmin.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/admin/newpost', name: 'app_post_admin_new', methods: ['GET', 'POST'])]
    public function newpostadmin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/newpost.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }


}
