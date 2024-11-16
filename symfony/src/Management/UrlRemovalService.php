<?php

declare(strict_types=1);

namespace App\Management;

use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Service\Notifications\MessengerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Mx\CreatorUrlsRemovalData;
use App\Utils\Mx\GroupedUrl;
use App\Utils\Mx\GroupedUrls;
use App\ValueObject\Notification;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Psl\Iter;
use Psl\Vec;
use Symfony\Component\Routing\RouterInterface;

final class UrlRemovalService
{
    private const array IGNORED_URL_TYPES = [
        Field::URL_COMMISSIONS,
        Field::URL_FURSUITREVIEW,
        Field::URL_FURTRACK,
        Field::URL_MINIATURES,
        Field::URL_OTHER,
        Field::URL_PHOTOS,
        Field::URL_SCRITCH,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $websiteShortName,
        private readonly string $primaryBaseUrl,
        private readonly RouterInterface $router,
        private readonly MessengerInterface $messenger,
    ) {
    }

    /**
     * @param string[] $urlIdsForRemoval
     */
    public static function getRemovalDataFor(Creator $creator, array $urlIdsForRemoval): CreatorUrlsRemovalData
    {
        $urls = GroupedUrls::from($creator);

        if ([] === $urlIdsForRemoval) {
            throw new InvalidArgumentException('No URL ID(s) to remove');
        }

        $removedUrls = $urls->onlyWithIds($urlIdsForRemoval);
        $remainingUrls = $urls->minus($removedUrls);

        if (count($urlIdsForRemoval) !== count($removedUrls->urls)) {
            throw new InvalidArgumentException('Referenced invalid URL ID(s) to remove');
        }

        // If there are no remaining valid URLs, hide the creator.
        $hide = [] === Vec\filter(
            $remainingUrls->urls,
            fn (GroupedUrl $url): bool => !Iter\contains(self::IGNORED_URL_TYPES, $url->type),
        );

        $sendEmail = ContactPermit::isAtLeastCorrections($creator->getContactAllowed());

        return new CreatorUrlsRemovalData($removedUrls, $remainingUrls, $hide, $sendEmail);
    }

    public function handleRemoval(Creator $creator, CreatorUrlsRemovalData $data): void
    {
        $creator->setNotes($this->getNewNotes($creator, $data));
        $this->updateUrls($creator, $data);

        if ($data->hide) {
            $creator->setInactiveReason('All previously known websites/social accounts are no longer working or are inactive');
        }

        $this->entityManager->flush();

        if ($data->sendEmail) {
            $this->sendNotification($creator, $data);
        }
    }

    private function getNewNotes(Creator $creator, CreatorUrlsRemovalData $data): string
    {
        $oldNotes = trim($creator->getNotes());

        if ('' !== $oldNotes) {
            $oldNotes = "\n\n-----\n$oldNotes";
        }

        $dateAndTime = UtcClock::now()->format('Y-m-d H:i');

        return "On $dateAndTime UTC the following links have been found to no longer work or to be inactive".
            " and have been removed:\n".$this->getUrlsBulletList($data).$oldNotes;
    }

    private function updateUrls(Creator $creator, CreatorUrlsRemovalData $data): void
    {
        foreach (Fields::urls() as $urlType) {
            $creator->set($urlType, $data->remainingUrls->getStringOrStrList($urlType));
        }
    }

    private function sendNotification(Creator $creator, CreatorUrlsRemovalData $data): void
    {
        $cardUrl = $this->primaryBaseUrl.$this->router->generate(RouteName::MAIN,
            ['_fragment' => $creator->getLastMakerId()], RouterInterface::ABSOLUTE_PATH);
        $updateUrl = $this->primaryBaseUrl.$this->router->generate(RouteName::IU_FORM_START,
            ['makerId' => $creator->getLastMakerId()], RouterInterface::ABSOLUTE_PATH);
        $contactUrl = $this->primaryBaseUrl.$this->router->generate(RouteName::CONTACT,
            [], RouterInterface::ABSOLUTE_PATH);

        $subject = $data->hide ? "Your card at $this->websiteShortName has been hidden"
            : "Your information at $this->websiteShortName may require your attention";

        $contents = "Hello {$creator->getName()}!";

        $links = $data->hide ? 'All the links provided previously' : 'The following links';

        $contents .= "\n\nYour information at $this->websiteShortName ( $cardUrl ) may require your attention.".
            " $links were found to be either no longer working, or to lead to inactive social accounts,".
            ' and so have been removed:'.
            "\n".$this->getUrlsBulletList($data);

        if ($data->hide) {
            $contents .= "\n\nSince the remaining information+links on your card are not sufficient,".
                ' your card has been hidden.';
        }

        $optionalAndRestore = $data->hide ? " (and restore your card's visibility)" : '';
        $orAnd = $data->hide ? 'and' : 'or';

        $contents .= "\n\nFeel free to send new links$optionalAndRestore $orAnd update any other information ".
            "at any time by using the following form:\n$updateUrl";

        $contents .= "\n\nIf you have any questions or need help with $this->websiteShortName, please do not hesitate"
            .' to initiate contact using any means listed on this page:'
            ."\n$contactUrl";

        $this->messenger->send(new Notification($subject, $contents)); // TODO: , recipient: $creator->getEmailAddress()
    }

    private function getUrlsBulletList(CreatorUrlsRemovalData $data): string
    {
        return implode("\n", array_unique(Vec\map(
            $data->removedUrls->urls,
            fn (GroupedUrl $url): string => "- $url->url",
        )));
    }
}
