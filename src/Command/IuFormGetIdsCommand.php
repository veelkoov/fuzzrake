<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\WebpageSnapshotManager;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\GoogleForms\Form;
use App\Utils\GoogleForms\Item;
use App\Utils\JsonException;
use App\Utils\Regexp\Utils as Regexp;
use App\Utils\Utils;
use App\Utils\Web\UrlFetcherException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;

class IuFormGetIdsCommand extends Command
{
    protected static $defaultName = 'app:iu-form:get-ids';
    private static $gfHelperFilePath = __DIR__.'/../../assets/js/main/GoogleFormsHelper.ts';

    /**
     * @var string
     */
    private $iuFormUrl;

    /**
     * @var WebpageSnapshotManager
     */
    private $snapshotManager;

    public function __construct(WebpageSnapshotManager $snapshotManager, string  $iuFormUrl)
    {
        parent::__construct();

        $this->iuFormUrl = $iuFormUrl;
        $this->snapshotManager = $snapshotManager;
    }

    protected function configure()
    {
        $this->setDescription('Fetch ID of I/U form\'s fields');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $snapshot = $this->snapshotManager->get($this->iuFormUrl, 'N/A');
        } catch (UrlFetcherException $e) {
            $io->error('Failed fetching the form: '.$e->getMessage());

            return 1;
        }

        $crawler = (new Crawler($snapshot->getContents()))->filter('body script:not(#base-js)');

        if (1 !== $crawler->count()) {
            $io->error('Failed to filter single script tag, got: '.$crawler->count());

            return 1;
        }

        try {
            $data = Utils::fromJson(Regexp::replace('#^var FB_PUBLIC_LOAD_DATA_ = #', '', rtrim($crawler->html(), ";\n\t\r ")));
        } catch (JsonException $e) {
            $io->error('Failed parsing JSON: '.$e->getMessage());

            return 1;
        }

        $helperFileContents = file_get_contents(self::$gfHelperFilePath);
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
                    $helperFileContents = $this->updateFieldId($helperFileContents, $field, $question->getOnlyAnswer()->getId());
                } else {
                    $io->warning('To be updated manually: '.$question->getOnlyAnswer()->getOnlyOption()->getName().' '.$question->getOnlyAnswer()->getId());
                }
            }

            unset($questionsLeftToMatch[$question->getIndex()]);

            file_put_contents(self::$gfHelperFilePath, $helperFileContents);
        }

        if (!empty($questionsLeftToMatch)) {
            $io->error("Didn't match the following questions: ".join(', ', $questionsLeftToMatch));

            return 1;
        }

        return 0;
    }

    private function updateFieldId(string $helperFileContents, Field $field, int $newId): string
    {
        return Regexp::replace('#(?<=\s)\d+(: (?:this\.transform[a-z]+\(?)?artisan\.'.preg_quote($field->modelName()).'\)?,)#i',
            $newId.'$1', $helperFileContents);
    }
}
