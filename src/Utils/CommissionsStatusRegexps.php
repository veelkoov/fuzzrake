<?php

namespace App\Utils;

class CommissionsStatusRegexps
{
    const HTML_CLEANER_REGEXPS = [
        '#</?(strong|b|i|span|center|a|em|font)[^>]*>#s' => '',
        '#(\s|&nbsp;|<br\s*/?>)+#s' => ' ',
        '#<style[^>]*>.*?</style>#s' => '',
        '# style="[^"]*"( (?=\>))?#s' => '',
        '#’|&\#39;#' => '\'',
    ];
    const COMMON_REPLACEMENTS = [
        'commissions' => 'comm?iss?ions?',
        'OPEN' => '(open(?!ing)|(?!not? |aren\'t |are not? )(accepting|considering)|WE_CAN take)',
        'CLOSED' => '(closed?|(not?|aren\'t|are not?|no longer|don\'t) (CURRENTLY )?(accepting|seeking|taking( on)?|take( on)?)|can(\'| ?no)t open)',
        'fursuits' => 'fursuits?',
        '</div>' => ' ?</div> ?',
        '<div>' => ' ?<div( class="[^"]*")?> ?',
        '<p>' => ' ?<p( class="[^"]*")?> ?',
        '</p>' => ' ?</p> ?',
        'WE_CAN' => '(i|we) can(?! not? )',
        'WE_ARE' => '(we are|we\'re|i am|i\'?m|STUDIO_NAME (is|are))',
        'WE' => '(i|we)',
        'MONTHS' => '(january|jan|february|feb|march|mar|april|apr|may|may|june|jun|july|jul|august|aug|september|sep|sept|october|oct|november|nov|december|dec)',
        'CURRENTLY' => '(current(ly)?|(right )?now|at (this|the) time|for the time being)',
    ];
    const FALSE_POSITIVES_REGEXES = [
        '(once|when) (WE_ARE STATUS for commissions|commissions are STATUS)',
        'art commissions: STATUS',
        'commissions STATUS MONTHS',
    ];
    const GENERIC_REGEXES = [
        '((WE_ARE )?CURRENTLY|(CURRENTLY )?WE_ARE) \**STATUS\**( for)?( the| new| some| all| any more)?( fursuits)? (commissions|projects|orders|quotes)( requests)?',
        'commissions((/| and | )quotes)?( status| are)?( ?:| now| currently ?:?| at this time are| permanently| (now )?indefinitely)? ?STATUS',
        'quotes have now STATUS',
        '(?!will not be )STATUS for (new )?(quotes and )?commissions ?([.!]|</)',
        'STATUS for (new )?(quotes and )?commissions ?([.!]|</)',
        'quote reviews are STATUS!',
        '(fursuits )?commissions(:? are( always)?| info)? STATUS',
        '(^|\.) ?STATUS for commissions ?($|[.(])',
        '<div>currently</div><div>STATUS</div><div>for commissions</div>',
        '<p>commissions are</p><p>STATUS</p>',
        '<p>status: STATUS</p>',
        '\[ commissions[. ]+STATUS \]',
        '<div class="([^"]*[^a-z])?commissions-STATUS"></div>',
        '<h2[^>]*>STATUS</h2>',
        'slots CURRENTLY STATUS',
        'STATUS commissions',
        'WE_ARE (CURRENTLY|also) STATUS for (everything|commissions)',
        'WE_ARE STATUS for all costume, mascot and fursuit work CURRENTLY',
        'WE STATUS\.',
        'CURRENTLY commissions status: fursuits STATUS',
    ];
}
