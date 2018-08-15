<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtensions extends AbstractExtension
{
    public function getFilters()
    {
        return array(
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
}
