<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Form\QueryType;
use App\Repository\ArtisanRepository;
use App\Service\EnvironmentsService;
use App\Utils\DataQuery;
use App\ValueObject\Routing\RouteName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/query')]
class QueryController extends AbstractController
{
    #[Route(path: '/', name: RouteName::MX_QUERY)]
    #[Cache(maxage: 0, public: false)]
    public function query(Request $request, ArtisanRepository $artisanRepository, EnvironmentsService $environments): Response
    {
        if (!$environments->isDevOrTest()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(QueryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $query = new DataQuery($form->get(QueryType::ITEM_QUERY)->getData());
            $query->run($artisanRepository);
        } else {
            $query = new DataQuery('');
        }

        return $this->renderForm('mx/query/index.html.twig', [
            'form'   => $form,
            'query'  => $query,
        ]);
    }
}
