<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanRepository;
use App\Utils\Json;
use App\Utils\Regexp\Regexp;
use App\Utils\Regexp\RegexpMatchException;
use App\Utils\StrUtils;
use App\Utils\Web\HttpClient\GentleHttpClient;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use LogicException;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DataSetScritchMiniaturesCommand extends Command
{
    private const PICTURE_URL_REGEXP = '#^https://scritch\.es/pictures/(?<picture_id>[a-z0-9-]{36})$#';

    protected static $defaultName = 'app:data:set-scritch-miniatures';

    private ArtisanRepository $artisanRepository;
    private EntityManagerInterface $entityManager;
    private CookieJar $cookieJar;
    private GentleHttpClient $httpClient;

    public function __construct(ArtisanRepository $artisanRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->artisanRepository = $artisanRepository;
        $this->entityManager = $entityManager;
        $this->cookieJar = new CookieJar();
        $this->httpClient = new GentleHttpClient();
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $response = $this->httpClient->get('https://scritch.es/', $this->cookieJar);

        $this->updateCookies($response);
        $csrfToken = $this->cookieJar->get('csrf-token')->getValue();

        foreach ($this->artisanRepository->getAll() as $artisan) {
            $pictureUrls = array_filter(explode("\n", $artisan->getScritchPhotoUrls()));

            if (empty($pictureUrls)) {
                $artisan->setScritchMiniatureUrls('');
                continue;
            }

            if (count($pictureUrls) === count(array_filter(explode("\n", $artisan->getScritchMiniatureUrls())))) {
                continue;
            }

            try {
                $miniatureUrls = $this->retrieveMiniatureUrls($pictureUrls, $csrfToken);
            } catch (ExceptionInterface | RegexpMatchException | JsonException | LogicException $e) {
                $io->error('Failed: '.$artisan->getLastMakerId().', '.$e->getMessage());
                continue;
            }

            $artisan->setScritchMiniatureUrls(implode("\n", $miniatureUrls));
            $io->writeln('Retrieved miniatures for '.StrUtils::artisanNamesSafeForCli($artisan));
        }

        if ($input->getOption('commit')) {
            $this->entityManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }

    /**
     * @param string[] $pictureUrls
     *
     * @return string[]
     *
     * @throws JsonException
     * @throws RegexpMatchException
     * @throws LogicException
     * @throws ExceptionInterface
     */
    private function retrieveMiniatureUrls(array $pictureUrls, string $csrfToken): array
    {
        $pictureIds = $this->idsFromPicureUrls($pictureUrls);
        $jsonPayloads = array_map([$this, 'getGraphQlJsonPayload'], $pictureIds);
        $result = [];

        foreach ($jsonPayloads as $jsonPayload) {
            $response = $this->httpClient->post('https://scritch.es/graphql', $jsonPayload, $this->cookieJar, [
                'Content-Type'  => 'application/json',
                'X-CSRF-Token'  => $csrfToken,
                'authorization' => "Scritcher $csrfToken",
            ]);

            $this->updateCookies($response);

            $thumbnailUrl = Json::decode($response->getContent(true))['data']['medium']['thumbnail'] ?? '';

            if ('' === $thumbnailUrl) {
                throw new LogicException("No thumbnail URL found in response: $response");
            }

            $result[] = $thumbnailUrl;
        }

        return $result;
    }

    /**
     * @param string[] $pictureUrls
     *
     * @return string[]
     *
     * @throws RegexpMatchException From inside array_map
     */
    private function idsFromPicureUrls(array $pictureUrls): array
    {
        $result = [];

        foreach ($pictureUrls as $pictureUrl) {
            $result[] = Regexp::requireMatch(self::PICTURE_URL_REGEXP, $pictureUrl)['picture_id'];
        }

        return $result;
    }

    private function getGraphQlJsonPayload(string $pictureId): string
    {
        return '{"operationName": "Medium", "variables": {"id": "'.$pictureId.'"}, "query": "query Medium($id: ID!, $tagging: Boolean) { medium(id: $id, tagging: $tagging) { thumbnail } }"}';
    }

    /**
     * @throws ExceptionInterface
     */
    private function updateCookies(ResponseInterface $response): void
    {
        $this->cookieJar->updateFromSetCookie($response->getHeaders(true)['set-cookie'] ?? []);
    }
}
