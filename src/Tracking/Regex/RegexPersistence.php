<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

use App\Entity\TrackerSetting;
use App\Repository\TrackerSettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RegexPersistence implements RegexesProvider
{
    private const GROUP_REGEXES = 'REGEXES';
    private const KEY_OFFER_STATUS = 'OFFER_STATUS';
    private const KEY_FALSE_POSITIVE = 'FALSE-POSITIVE';
    private const GROUP_TRANSLATIONS = 'TRANSLATIONS';
    private const GROUP_CLEANERS = 'CLEANERS';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TrackerSettingRepository $settingRepository,
        private readonly RegexFactory $patternFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getRegexes(): Regexes
    {
        $falsePositives = [];
        $offerStatuses = [];
        $groupTranslations = [];
        $cleaners = [];

        foreach ($this->settingRepository->findAll() as $setting) {
            if (self::GROUP_REGEXES === $setting->getGroup()) {
                if (self::KEY_OFFER_STATUS === $setting->getKey()) {
                    $offerStatuses[] = $setting->getValue();
                } elseif (self::KEY_FALSE_POSITIVE === $setting->getKey()) {
                    $falsePositives[] = $setting->getValue();
                } else {
                    $this->logger->warning('Retrieved unsupported regex item from the settings table', ['entity' => $setting]);
                }
            } elseif (self::GROUP_TRANSLATIONS === $setting->getGroup()) {
                if (!array_key_exists($setting->getKey(), $groupTranslations)) {
                    $groupTranslations[$setting->getKey()] = [];
                }

                $groupTranslations[$setting->getKey()][] = $setting->getValue();
            } elseif (self::GROUP_CLEANERS === $setting->getGroup()) {
                $cleaners[$setting->getKey()] = $setting->getValue();
            } else {
                $this->logger->warning('Retrieved unsupported item from the settings table', ['entity' => $setting]);
            }
        }

        return new Regexes($falsePositives, $offerStatuses, $groupTranslations, $cleaners);
    }

    public function rebuild(): void
    {
        $this->settingRepository->removeAll();

        foreach ($this->patternFactory->getOfferStatuses() as $regex) {
            $setting = (new TrackerSetting())
                ->setGroup(self::GROUP_REGEXES)
                ->setKey(self::KEY_OFFER_STATUS)
                ->setValue($regex);

            $this->entityManager->persist($setting);
        }

        foreach ($this->patternFactory->getFalsePositives() as $regex) {
            $setting = (new TrackerSetting())
                ->setGroup(self::GROUP_REGEXES)
                ->setKey(self::KEY_FALSE_POSITIVE)
                ->setValue($regex);

            $this->entityManager->persist($setting);
        }

        foreach ($this->patternFactory->getGroupTranslations() as $groupName => $translations) {
            foreach ($translations as $translation) {
                $setting = (new TrackerSetting())
                    ->setGroup(self::GROUP_TRANSLATIONS)
                    ->setKey($groupName)
                    ->setValue($translation);

                $this->entityManager->persist($setting);
            }
        }

        foreach ($this->patternFactory->getCleaners() as $subject => $replacement) {
            $setting = (new TrackerSetting())
                ->setGroup(self::GROUP_CLEANERS)
                ->setKey($subject)
                ->setValue($replacement);

            $this->entityManager->persist($setting);
        }

        $this->entityManager->flush();
    }
}
