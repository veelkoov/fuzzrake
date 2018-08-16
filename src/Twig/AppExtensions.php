<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtensions extends AbstractExtension
{
    const MONTHS = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    public function getFilters()
    {
        return array(
            new TwigFilter('since', array($this, 'sinceFilter')),
            new TwigFilter('other', array($this, 'otherFilter')),
        );
    }

    public function otherFilter($primaryList, $otherList)
    {
        if ($otherList !== '') {
            if ($primaryList !== '') {
                return "$primaryList, Other";
            } else {
                return 'Other';
            }
        } else {
            return $primaryList;
        }
    }

    public function sinceFilter($input)
    {
        if ($input === '') {
            return '';
        }

        if (!preg_match('#^(?<year>\d{4})-(?<month>\d{2})$#', $input, $zapałki)) {
            throw new TplDataException("Invalid 'since' data: '$input''");
        }

        return self::MONTHS[(int)$zapałki['month']] . ' ' . $zapałki['year'];
    }
}
