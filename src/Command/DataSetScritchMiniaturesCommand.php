<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanRepository;
use App\Utils\Json;
use App\Utils\Regexp\RegexpMatchException;
use App\Utils\Regexp\Utils as Regexp;
use App\Utils\Web\GentleHttpClient;
use App\Utils\Web\HttpClientException;
use App\Utils\Web\TmpCookieJar;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataSetScritchMiniaturesCommand extends Command
{
    private const PICTURE_URL_REGEXP = '#^https://scritch.es/pictures/(?<picture_id>[a-z0-9-]{36})$#';

    protected static $defaultName = 'app:data:set-scritch-miniatures';

    private ArtisanRepository $artisanRepository;
    private EntityManagerInterface $objectManager;

    public function __construct(ArtisanRepository $artisanRepository, EntityManagerInterface $objectManager)
    {
        parent::__construct();

        $this->artisanRepository = $artisanRepository;
        $this->objectManager = $objectManager;
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
    }

    /**
     * @return int|void|null
     *
     * @throws HttpClientException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $cookieJar = new TmpCookieJar();
        $httpClient = new GentleHttpClient($cookieJar);
        $httpClient->get('https://scritch.es/');
        $csrfToken = $cookieJar->getValue('csrf-token');

        foreach ($this->artisanRepository->getAll() as $artisan) {
            $pictureUrls = explode("\n", $artisan->getScritchPhotoUrls());

            if (empty($pictureUrls)) {
                $artisan->setScritchMiniatureUrls('');
                continue;
            }

            if (count($pictureUrls) === count(explode("\n", $artisan->getScritchMiniatureUrls()))) {
                continue;
            }

            try {
                $miniatureUrls = $this->retrieveMiniatureUrls($pictureUrls, $httpClient, $csrfToken);
            } catch (HttpClientException | RegexpMatchException | JsonException | LogicException $e) {
                $io->error('Failed: '.$artisan->getLastMakerId().', '.$e->getMessage());
                continue;
            }

            $artisan->setScritchMiniatureUrls(implode("\n", $miniatureUrls));
        }

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }

    /**
     * @param string[] $pictureUrls
     *
     * @throws HttpClientException  From inside array_map
     * @throws JsonException        From inside array_map
     * @throws RegexpMatchException
     * @throws LogicException
     */
    private function retrieveMiniatureUrls(array $pictureUrls, GentleHttpClient $httpClient, string $csrfToken): array
    {
        $pictureIds = $this->idsFromPicureUrls($pictureUrls);
        $jsonPayloads = array_map([$this, 'getGraphQlJsonPayload'], $pictureIds);

        return array_map(function (string $jsonPayload) use ($httpClient, $csrfToken): string {
            $responseJson = $httpClient->post('https://scritch.es/graphql', $jsonPayload, [
                'Content-Type'  => 'application/json',
                'X-CSRF-Token'  => $csrfToken,
                'authorization' => "Scritcher $csrfToken",
            ]);

            $result = Json::decode($responseJson)['data']['medium']['thumbnail'] ?? '';

            if ('' === $result) {
                throw new LogicException("No thumbnail URL found in response: $responseJson");
            }

            return $result;
        }, $jsonPayloads);
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
        return array_map([$this, 'idFromPictureUrl'], $pictureUrls);
    }

    private function getGraphQlJsonPayload(string $pictureId): string
    {
        return '{"operationName": "Medium", "variables": {"id": "'.$pictureId.'"}, "query": "query Medium($id: ID!, $tagging: Boolean) { medium(id: $id, tagging: $tagging) { thumbnail } }"}';
    }

    /**
     * @throws RegexpMatchException
     */
    private function idFromPictureUrl(string $pictureUrl): string
    {
        $matches = Regexp::requireMatch(self::PICTURE_URL_REGEXP, $pictureUrl);

        return $matches['picture_id'];
    }
}
