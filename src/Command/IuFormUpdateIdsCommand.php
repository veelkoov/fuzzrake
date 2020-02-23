<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\WebpageSnapshotManager;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\GoogleForms\Form;
use App\Utils\GoogleForms\Item;
use App\Utils\Json;
use App\Utils\Regexp\Regexp;
use App\Utils\Web\FreeUrl;
use JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class IuFormUpdateIdsCommand extends Command
{
    protected static $defaultName = 'app:iu-form:update-ids';
    private static string $updatedFilePath = __DIR__.'/../Service/IuFormService.php';

    private string $iuFormUrl;
    private WebpageSnapshotManager $snapshotManager;

    public function __construct(WebpageSnapshotManager $snapshotManager, string $iuFormUrl)
    {
        parent::__construct();

        $this->iuFormUrl = $iuFormUrl;
        $this->snapshotManager = $snapshotManager;
    }

    protected function configure()
    {
        $this->setDescription('Fetch ID of I/U form\'s fields');
        $this->addOption('refresh', 'r', null, 'Refresh pages in cache (re-fetch)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $snapshot = $this->snapshotManager->get(new FreeUrl($this->iuFormUrl), $input->getOption('refresh'), true);
        } catch (ExceptionInterface $e) {
            $io->error('Failed fetching the form: '.$e->getMessage());

            return 1;
        }

        $crawler = (new Crawler($snapshot->getContents()))->filter('body script:not(#base-js)');

        if (1 !== $crawler->count()) {
            $io->error('Failed to filter single script tag, got: '.$crawler->count());

            return 1;
        }

        try {
            $data = Json::decode(Regexp::replace('#^var FB_PUBLIC_LOAD_DATA_ = #', '', rtrim($crawler->html(), ";\n\t\r ")));
        } catch (JsonException $e) {
            $io->error('Failed parsing JSON: '.$e->getMessage());

            return 1;
        }

        $updatedFileContents = file_get_contents(self::$updatedFilePath);
        $form = new Form($data);

        $questionsLeftToMatch = array_filter($form->getItems(), function (Item $item) {
            return $item->isFillable();
        });

        foreach (Fields::inIuForm() as $field) {
            if (empty($field->iuFormRegexp())) {
                continue;
            }

            /**
             * @var Item[]
             */
            $matches = array_filter($questionsLeftToMatch, function (Item $question) use ($field) {
                return Regexp::match($field->iuFormRegexp(), $question->getName());
            });

            if (1 !== count($matches)) {
                $io->error('Expected exactly one match for field '.$field->name().', got '.count($matches).': '.implode(', ', $matches));

                return 1;
            }

            $question = array_pop($matches);

            if ($field->exportToIuForm()) {
                if ($field->is(Fields::CONTACT_INPUT_VIRTUAL)) {
                    $field = Fields::get(Fields::CONTACT_INFO_OBFUSCATED);
                }

                if ($field->modelName()) {
                    $updatedFileContents = $this->updateFieldId($updatedFileContents, $field, $question->getOnlyAnswer()->getId());
                } else {
                    $io->warning('To be updated manually: '.$question->getOnlyAnswer()->getOnlyOption()->getName().' '.$question->getOnlyAnswer()->getId());
                }
            }

            unset($questionsLeftToMatch[$question->getIndex()]);
        }

        if (false === file_put_contents(self::$updatedFilePath, $updatedFileContents)) {
            $io->error('Failed to write the file');

            return 1;
        }

        if (!empty($questionsLeftToMatch)) {
            $io->error('Didn\'t match the following questions: '.join(', ', $questionsLeftToMatch));

            return 1;
        }

        return 0;
    }

    private function updateFieldId(string $helperFileContents, Field $field, int $newId): string
    {
        return Regexp::replace('#(?<=\s)\d+( +=> +(?:\$this->transform[a-z]+\(?)?\$artisan->get'.preg_quote(ucfirst($field->modelName())).'\(\)\)?,)#i',
            $newId.'$1', $helperFileContents);
    }
}
