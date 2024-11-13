<?php

declare(strict_types=1);

namespace App\Management;

use App\Data\Definitions\Fields\Fields;
use App\Service\Notifications\EmailService;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\Mx\CreatorUrlsRemovalData;
use App\Utils\Mx\GroupedUrl;
use App\ValueObject\Notification;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use Psl\Vec;
use Symfony\Component\Routing\RouterInterface;

final class UrlRemovalService // TODO: Tests
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $websiteShortName,
        private readonly string $primaryBaseUrl,
        private readonly RouterInterface $router,
        private readonly EmailService $emailService,
    ) {
    }

    public function handleRemoval(Creator $creator, CreatorUrlsRemovalData $data): void
    {
        $creator->setNotes($this->getNewNotes($creator, $data));
        $this->updateUrls($creator, $data);

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

        return 'On YYYY-MM-DD HH:mm:ss UTC the following links were determined to be no longer working/active'.
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
        $updateUrl = $this->primaryBaseUrl.$this->router->generate(RouteName::IU_FORM_START,
            ['makerId' => $creator->getLastMakerId()], RouterInterface::ABSOLUTE_PATH);
        $contactUrl = $this->primaryBaseUrl.$this->router->generate(RouteName::CONTACT,
            [], RouterInterface::ABSOLUTE_PATH);

        $subject = $data->hide ? "Your card at $this->websiteShortName has been hidden - action required"
            : "Your information at $this->websiteShortName may require your attention";

        $contents = "Hello {$creator->getName()}!\n\nThe following links on your card at $this->websiteShortName".
            " have been determined to be no longer working/active and have been removed:\n"
            .$this->getUrlsBulletList($data);

        if ($data->hide) {
            $contents .= "\n\nSince the remaining information does not meet the requirements, your card has been hidden.";
        }

        $optionalAndRestore = $data->hide ? " (and restore your card's visibility)" : '';

        $contents .= "\n\nYou can always update your information$optionalAndRestore by using the following link:"
            ."\n$updateUrl";

        $contents .= "\n\nIf you have any questions or need help with $this->websiteShortName, please do not hesitate"
            .' to initiate contact using any means listed on this page:'
            ."\n$contactUrl";

        $this->emailService->send(new Notification($subject, $contents)); // TODO: Recipient
    }

    private function getUrlsBulletList(CreatorUrlsRemovalData $data): string
    {
        return implode("\n", array_unique(Vec\map(
            $data->removedUrls->urls,
            fn (GroupedUrl $url): string => "- $url->url",
        )));
    }
}
