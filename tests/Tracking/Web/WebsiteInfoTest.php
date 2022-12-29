<?php

declare(strict_types=1);

namespace App\Tests\Tracking\Web;

use App\Tracking\Web\WebpageSnapshot\Snapshot;
use App\Tracking\Web\WebsiteInfo;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class WebsiteInfoTest extends TestCase
{
    public function testGetChildrenUrlsForWixsite(): void
    {
        $snapshots = new Snapshot('', '', UtcClock::now(), '', 200, [], []);

        $subject = new WebsiteInfo();

        self::assertEquals([], $subject->getChildrenUrls($snapshots));
    }

    public function testGetChildrenUrlsForTrello(): void
    {
        $snapshots = new Snapshot('', 'https://trello.com/b/9QrnqQ1j/phasmophobia', UtcClock::now(), '', 200, [], []);

        $subject = new WebsiteInfo();

        self::assertEquals(['https://trello.com/1/Boards/9QrnqQ1j?lists=open&list_fields=name&cards=visible&card_attachments=false&card_stickers=false&card_fields=desc%2CdescData%2Cname&card_checklists=none&members=none&member_fields=none&membersInvited=none&membersInvited_fields=none&memberships_orgMemberType=false&checklists=none&organization=false&organization_fields=none%2CdisplayName%2Cdesc%2CdescData%2Cwebsite&organization_tags=false&myPrefs=false&fields=name%2Cdesc%2CdescData'], $subject->getChildrenUrls($snapshots));
    }

    public function testGetChildrenUrlsForInstagram(): void
    {
        $snapshots = new Snapshot('', 'https://www.instagram.com/getfursu.it/', UtcClock::now(), '', 200, [], []);

        $subject = new WebsiteInfo();

        self::assertEquals(['https://i.instagram.com/api/v1/users/web_profile_info/?username=getfursu.it'], $subject->getChildrenUrls($snapshots));
    }
}
