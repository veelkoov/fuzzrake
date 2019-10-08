<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanUrlRepository;
use App\Utils\Web\GentleHttpClient;
use App\Utils\Web\TmpCookieJar;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataSetScritchMiniaturesCommand extends Command
{
    protected static $defaultName = 'app:data:set-scritch-miniatures';

    /**
     * @var ArtisanUrlRepository
     */
    private $artisanUrlRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(ArtisanUrlRepository $artisanUrlRepository, ObjectManager $objectManager)
    {
        parent::__construct();

        $this->artisanUrlRepository = $artisanUrlRepository;
        $this->objectManager = $objectManager;

        $this->httpClient = new GentleHttpClient(new TmpCookieJar());
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // TODO

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }
    }
}

/*

csrf="$(echo -en "$(curl -c cookies.txt -v https://scritch.es/ 2>&1 | grep 'Set-Cookie: csrf-token=' | cut -f2 -d'=' | cut -f1 -d';' | sed 's/+/ /g;s/%\(..\)/\\x\1/g;')")"

curl -b cookies.txt -v https://scritch.es/graphql -d '{"operationName": "Medium", "variables": {"id":"c980fb70-3478-4f90-ad2b-c6e73f50e270"}, "query": "query Medium($id: ID!, $tagging: Boolean) { medium(id: $id, tagging: $tagging) { thumbnail } }"}' -H 'Content-Type: application/json' -H "X-CSRF-Token: $csrf" -H "authorization: Scritcher $csrf"

 */
