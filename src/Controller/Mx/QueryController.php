<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Form\QueryType;
use App\Repository\ArtisanRepository;
use App\Service\HostsService;
use App\Utils\Regexp\Regexp;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mx/query")
 */
class QueryController extends AbstractController
{
    /**
     * @Route("/", name="mx_query")
     * @Cache(maxage=0, public=false)
     */
    public function ordering(Request $request, ArtisanRepository $artisanRepository, HostsService $hostsSrv): Response
    {
        if (!$hostsSrv->isDevMachine()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(QueryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $query = array_filter(Regexp::split('#\s+#', $form->get(QueryType::ITEM_QUERY)->getData()));
            $result = $artisanRepository->getOthersLike($query);
        } else {
            $query = [];
            $result = [];
        }

        return $this->render('mx/query/index.html.twig', [
            'form'   => $form->createView(),
            'query'  => $query,
            'result' => $result,
        ]);
    }
}
