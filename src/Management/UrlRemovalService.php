<?php

declare(strict_types=1);

namespace App\Management;

use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Service\EmailService;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Mx\CreatorUrlsRemovalData;
use App\Utils\Mx\GroupedUrl;
use App\Utils\Mx\GroupedUrls;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Veelkoov\Debris\Lists\StringList;

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
        #[Autowire(env: 'WEBSITE_SHORT_NAME')]
        private readonly string $websiteShortName,
        #[Autowire(env: 'PRIMARY_BASE_URL')]
        private readonly string $primaryBaseUrl,
        private readonly RouterInterface $router,
        private readonly EmailService $emailService,
    ) {
    }

    public static function getRemovalDataFor(Creator $creator, StringList $urlIdsForRemoval): CreatorUrlsRemovalData
    {
        $urls = GroupedUrls::from($creator);

        if ($urlIdsForRemoval->isEmpty()) {
            throw new InvalidArgumentException('No URL ID(s) to remove');
        }

        $removedUrls = $urls->onlyWithIds($urlIdsForRemoval);
        $remainingUrls = $urls->onlyWithoutIds($urlIdsForRemoval);

        if ($urlIdsForRemoval->count() !== $removedUrls->count()) {
            throw new InvalidArgumentException('Referenced invalid URL ID(s) to remove');
        }

        // If there are no remaining valid URLs, hide the creator.
        $hide = $remainingUrls->all(static fn (GroupedUrl $url) => arr_contains(self::IGNORED_URL_TYPES, $url->type));

        $sendEmail = ContactPermit::isAtLeastCorrections($creator->getContactAllowed());

        return new CreatorUrlsRemovalData($removedUrls, $remainingUrls, $hide, $sendEmail);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function handleRemoval(Creator $creator, CreatorUrlsRemovalData $data): void
    {
        if ($data->hide) {
            $data = $this->getDataWithTrackingUrlsRemoved($data);

            $creator->setInactiveReason('All previously known websites/social accounts are no longer working or are inactive');
        }

        $creator->setNotes($this->getNewNotes($creator, $data));
        $this->updateUrls($creator, $data);

        if ($data->sendEmail) {
            $this->sendNotification($creator, $data);
        }

        $this->entityManager->flush();
    }

    private function getNewNotes(Creator $creator, CreatorUrlsRemovalData $data): string
    {
        $oldNotes = trim($creator->getNotes());

        if ('' !== $oldNotes) {
            $oldNotes = "\n\n-----\n$oldNotes";
        }

        $dateAndTime = UtcClock::now()->format('Y-m-d H:i');

        return "On $dateAndTime UTC the following links have been found to no longer work or to be inactive".
            " and have been removed:\n".$this->getRemovedUrlsBulletList($data).$oldNotes;
    }

    private function updateUrls(Creator $creator, CreatorUrlsRemovalData $data): void
    {
        foreach (Fields::urls() as $urlType) {
            $newUrlsOrEmpty = $data->remainingUrls->getStringOrStrList($urlType);

            if ($data->hide && Field::URL_COMMISSIONS === $urlType) {
                $newUrlsOrEmpty = []; // Hidden makers should not be tracked, enforce removal
            }

            $creator->set($urlType, $newUrlsOrEmpty);
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendNotification(Creator $creator, CreatorUrlsRemovalData $data): void
    {
        $cardUrl = $this->primaryBaseUrl.$this->router->generate(RouteName::MAIN,
            ['_fragment' => $creator->getLastCreatorId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        $updateUrl = $this->primaryBaseUrl.$this->router->generate(RouteName::USER_IU_FORM_START,
            ['creatorId' => $creator->getLastCreatorId()], UrlGeneratorInterface::ABSOLUTE_PATH);
        $contactUrl = $this->primaryBaseUrl.$this->router->generate(RouteName::CONTACT,
            [], UrlGeneratorInterface::ABSOLUTE_PATH);

        $subject = $data->hide ? "Your card at $this->websiteShortName has been hidden"
            : "Your information at $this->websiteShortName may require your attention";

        $contents = "Hello {$creator->getName()}!";

        $links = $data->hide ? 'All the links provided previously' : 'The following links';

        $contents .= "\n\nYour information at $this->websiteShortName ( $cardUrl ) may require your attention.".
            " $links were found to be either no longer working, or to lead to inactive social accounts,".
            ' and so have been removed:'.
            "\n".$this->getRemovedUrlsBulletList($data);

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

        $this->emailService->send($subject, $contents, $creator->getEmailAddress());
    }

    private function getRemovedUrlsBulletList(CreatorUrlsRemovalData $data): string
    {
        return StringList::mapFrom($data->removedUrls, static fn (GroupedUrl $url) => "- $url->url")->join("\n");
    }

    private function getDataWithTrackingUrlsRemoved(CreatorUrlsRemovalData $data): CreatorUrlsRemovalData
    {
        $removedUrls = $data->removedUrls;
        $remainingUrls = $data->remainingUrls;
        $trackingUrls = $remainingUrls->filter(static fn (GroupedUrl $url) => Field::URL_COMMISSIONS === $url->type);

        return new CreatorUrlsRemovalData(
            $removedUrls->plusAll($trackingUrls)->freeze(),
            $remainingUrls->minusAll($trackingUrls)->freeze(),
            $data->hide,
            $data->sendEmail,
        );
    }
}
