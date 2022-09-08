<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Service\EnvironmentsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class FuzzrakeAbstractController extends AbstractController
{
    public function __construct(
        protected readonly EnvironmentsService $environments,
    ) {
    }

    protected function authorize(bool $shouldLetIn = true): void
    {
        if (!$this->environments->isDevOrTest() || !$shouldLetIn) {
            throw $this->createAccessDeniedException();
        }
    }
}
