<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Form\Mx\QueryType;
use App\Utils\DataQuery;
use App\Utils\Enforce;
use App\ValueObject\Routing\RouteName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mx/query')]
class QueryController extends FuzzrakeAbstractController
{
    #[Route(path: '/', name: RouteName::MX_QUERY)]
    #[Cache(maxage: 0, public: false)]
    public function query(Request $request): Response
    {
        $form = $this->createForm(QueryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $query = new DataQuery(Enforce::nString($form->get(QueryType::ITEM_QUERY)->getData()) ?? '');
            $query->run($this->creatorRepository);
        } else {
            $query = new DataQuery('');
        }

        return $this->render('mx/query/index.html.twig', [
            'form'  => $form,
            'query' => $query,
        ]);
    }
}
