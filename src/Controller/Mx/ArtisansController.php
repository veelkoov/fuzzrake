<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Entity\Artisan;
use App\Form\ArtisanType;
use App\Service\EnvironmentsService;
use App\Utils\Artisan\Fields;
use App\Utils\Artisan\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mx/artisans")
 */
class ArtisansController extends AbstractController
{
    /**
     * @Route("/{id}/edit", name="mx_artisan_edit", methods={"GET", "POST"})
     * @Route("/new", name="mx_artisan_new", methods={"GET", "POST"})
     * @Cache(maxage=0, public=false)
     */
    public function edit(Request $request, ?Artisan $artisan, EnvironmentsService $environments): Response
    {
        if (!$environments->isDevMachine()) {
            throw $this->createAccessDeniedException();
        }

        $artisan ??= new Artisan();
        $originalPasscode = $artisan->getPasscode();

        $form = $this->createForm(ArtisanType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $artisan->getId() && $form->get(ArtisanType::BTN_DELETE)->isClicked()) {
                $this->getDoctrine()->getManager()->remove($artisan);
            } else {
                Utils::updateContact($artisan, $artisan->getContactInfoOriginal());
                $this->fixNewlines($artisan);
                $this->restoreUnchangedPasscode($artisan, $originalPasscode);

                $this->getDoctrine()->getManager()->persist($artisan);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('main');
        }

        return $this->render('mx/artisans/edit.html.twig', [
            'artisan' => $artisan,
            'form'    => $form->createView(),
        ]);
    }

    private function restoreUnchangedPasscode(Artisan $artisan, string $originalPasscode): void
    {
        if (empty(trim($artisan->getPasscode()))) {
            $artisan->setPasscode($originalPasscode);
        }
    }

    private function fixNewlines(Artisan $artisan): void
    {
        foreach (Fields::persisted() as $field) {
            if (($value = $artisan->get($field)) && is_string($value)) {
                $artisan->set($field, str_replace("\r\n", "\n", $value));
            }
        }
    }
}
