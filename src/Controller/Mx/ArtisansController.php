<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Entity\Artisan;
use App\Form\ArtisanType;
use App\Service\HostsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mx/artisans")
 */
class ArtisansController extends AbstractController
{
//    /**
//     * @Route("/new", name="mx_artisan_new", methods={"GET","POST"})
//     */
//    public function new(Request $request): Response
//    {
//        $artisan = new Artisan();
//        $form = $this->createForm(ArtisanType::class, $artisan);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($artisan);
//            $entityManager->flush();
//
//            return $this->redirectToRoute('artisan_index');
//        }
//
//        return $this->render('artisan/new.html.twig', [
//            'artisan' => $artisan,
//            'form' => $form->createView(),
//        ]);
//    }

    /**
     * @Route("/{id}/edit", name="mx_artisan_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Artisan $artisan, HostsService $hostsSrv): Response
    {
        if (!$hostsSrv->isDevMachine()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ArtisanType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('main');
        }

        return $this->render('mx/artisans/edit.html.twig', [
            'artisan' => $artisan,
            'form'    => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="mx_artisan_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Artisan $artisan, HostsService $hostsSrv): Response
    {
        if (!$hostsSrv->isDevMachine()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$artisan->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($artisan);
            $entityManager->flush();
        }

        return $this->redirectToRoute('main');
    }
}
