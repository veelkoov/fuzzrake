<?php

declare(strict_types=1);

namespace App\Tasks\Miniatures;

use App\Tracking\Web\HttpClient\GentleHttpClient;
use App\Utils\UnbelievableRuntimeException;
use JsonException;
use LogicException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

abstract class AbstractMiniatures
{
    protected Pattern $pattern;

    public function __construct(
        protected GentleHttpClient $httpClient,
    ) {
        $this->pattern = pattern($this->getRegexp());
    }

    public function supportsUrl(string $url): bool
    {
        return $this->pattern->test($url);
    }

    protected function getPictureId(string $photoUrl): string
    {
        return $this->pattern->match($photoUrl)
            ->findFirst(function (Detail $detail): string {
                try {
                    return $detail->get('picture_id');
                } catch (NonexistentGroupException $e) {
                    throw new UnbelievableRuntimeException($e);
                }
            })->orElse(fn () => throw new LogicException("Failed to match picture URL: '$photoUrl'"));
    }

    /**
     * @throws ExceptionInterface|JsonException
     */
    abstract public function getMiniatureUrl(string $photoUrl): string;

    abstract protected function getRegexp(): string;
}
