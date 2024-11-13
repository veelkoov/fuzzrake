<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Form\Mx\CreatorUrlsRemovalType;
use App\Form\Mx\CreatorUrlsSelectionType;
use App\Utils\Mx\CreatorUrlsRemovalData;
use App\Utils\Mx\CreatorUrlsSelectionData;
use App\Utils\Mx\GroupedUrls;
use App\ValueObject\Routing\RouteName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mx/creator_urls')]
class CreatorUrlsController extends FuzzrakeAbstractController
{
    #[Route('/{creatorId}', name: RouteName::MX_CREATOR_URLS_SELECTION)]
    #[Cache(maxage: 0, public: false)]
    public function check(Request $request, string $creatorId): Response
    {
        $this->authorize();

        $creator = $this->getCreatorOrThrow404($creatorId);
        $urls = GroupedUrls::from($creator);

        $data = new CreatorUrlsSelectionData();
        $form = $this->createForm(CreatorUrlsSelectionType::class, $data, ['urls' => $urls]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $urlIds = implode(',', $data->getChosenUrls());

            return $this->redirectToRoute(RouteName::MX_CREATOR_URLS_REMOVAL, [
                'creatorId' => $creatorId,
                'urlIds'    => $urlIds,
            ]);
        }

        return $this->render('mx/creator_urls_selection.html.twig', [
            'creator' => $creator,
            'urls'    => $urls,
            'form'    => $form,
        ]);
    }

    #[Route('/{creatorId}/{urlIds}', name: RouteName::MX_CREATOR_URLS_REMOVAL)]
    #[Cache(maxage: 0, public: false)]
    public function removal(Request $request, string $creatorId, string $urlIds): Response
    {
        $this->authorize();

        $creator = $this->getCreatorOrThrow404($creatorId);

        $data = new CreatorUrlsRemovalData($creator, explode(',', $urlIds));
        $form = $this->createForm(CreatorUrlsRemovalType::class, $data);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            // TODO: Action

            return $this->redirectToRoute(RouteName::MAIN, ['_fragment' => $creatorId]);
        }

        return $this->render('mx/creator_urls_removal.html.twig', [
            'creator' => $creator,
            'form'    => $form,
            'result'  => $data,
        ]);
    }
}
