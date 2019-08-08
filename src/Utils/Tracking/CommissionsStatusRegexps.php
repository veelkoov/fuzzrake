<?php

namespace App\Utils\Tracking;

class CommissionsStatusRegexps
{
    const HTML_CLEANER_REGEXPS = [
        '#</?(strong|b|i|span|center|a|em|font)[^>]*>#s' => '',
        '#(\s|&nbsp;|<br\s*/?>)+#s'                      => ' ',
        '#<style[^>]*>.*?</style>#s'                     => '',
        '# style="[^"]*"( (?=\>))?#s'                    => '',
        '#’|&\#39;|&\#8217;#'                            => '\'',
        '<!--.*?-->'                                     => '',
        '# +data-[^>"]+ *= *"[^"]+" *#'                  => ' ',
    ];

    const COMMON_REPLACEMENTS = [
        'COMMISSIONS'       => '(quotes? reviews|everything|(quotes and )?comm?iss?i?ons?((/| and | )quotes)?|comms)',
        'OPEN'              => '(open(?!ing)|(?!not? (currently )?|aren\'t |are not? )(accepting|considering|taking)|WE_CAN take|live)',
        'CLOSED'            => '(closed?|(not?|aren\'t|are not?|no longer|don\'t) (TIMESPAN )?(do commissions|open|accepting|seeking|taking( on)?|take( on)?)|can(\'| ?no)t open|on hold)',
        'fursuits'          => 'fursuits?',
        '</(div|p|h[1-6])>' => ' ?</$1> ?',
        '<(div|p|h[1-6])>'  => ' ?<$1( class="[^"]{1,200}")?> ?',
        'WE_CAN'            => '(i|we) can(?! not? )',
        'WE_ARE'            => '(we are|we\'re|i am|i\'?m|STUDIO_NAME (is|are))',
        'WE'                => '(i|we)',
        'MONTHS'            => '(january|jan|february|feb|march|mar|april|apr|may|may|june|jun|july|jul|august|aug|september|sep|sept|october|oct|november|nov|december|dec)',
        'TIMESPAN'          => '(current(ly)?|(right )?now|at (this|the) time|for the time being|already|(now )?(always|permanently|indefinitely))',
        '<HTML_TAG>'        => '( ?<[^>]{1,200}> ?)',
    ];

    const FALSE_POSITIVES_REGEXES = [
        'FP01' => '(once|when) ((WE_ARE|WE) STATUS( for)? COMMISSIONS|COMMISSIONS are STATUS)',
        'FP02' => 'will not be STATUS for COMMISSIONS',
        'FP03' => '(art|painted glass|illustrations?) COMMISSIONS( status:| are|:) STATUS',
        'FP04' => 'COMMISSIONS (status:)?STATUS( in| for)?( late| early)? (MONTHS|20\d\d)',
        'FP05' => 'open for commissions\?</h[1-6]>',
        'FP06' => 'if WE_ARE STATUS (for )?(new )?COMMISSIONS',
        'FP07' => 'COMMISSIONS:? opens? (20[0-9]{2}|soon)',
    ];

    const GENERIC_REGEXES = [
        'G01' => '((WE_ARE )?(?<!not )TIMESPAN|(TIMESPAN )?WE_ARE) \**STATUS\**( for)?( the| new| some| all| any more)?( fursuits)? (COMMISSIONS|projects|orders|quotes|work)( requests)?',
        'G02' => 'COMMISSIONS( status|:? are| have| info)?( TIMESPAN)?[-: ]+(&gt;)*STATUS(&lt;)*',
        'G03' => 'quotes? have TIMESPAN STATUS',
        'G04' => 'order quotes are STATUS',
        'G05' => 'journals: \d+ favorites: \d+ STATUS commissions(?= </td>)', // FurAffinity right-top status
        'G06' => '(WE )?STATUS (for (new )?)?COMMISSIONS( \(limited\))? ?([.!*]|<HTML_TAG>)',
        'G07' => '<div>TIMESPAN</div><div>STATUS</div><div>for COMMISSIONS</div>',
        'G08' => 'COMMISSIONS (are|status)( TIMESPAN)?[: ]*<HTML_TAG>{1,5}(TIMESPAN )?STATUS',
        'G09' => '<h2>"cawmission" status</h2><div>STATUS',
        'G10' => '<p>status: STATUS</p>',
        'G11' => '(TIMESPAN|fursuits)( mode)?: STATUS',
        'G12' => '<div class="([^"]*[^a-z])?COMMISSIONS-STATUS"></div>',
        'G13' => '<h2>STATUS</h2>',
        'G14' => '(>|[1-9]\d*/\d+ )slots?( TIMESPAN( -)?)? STATUS',
        'G15' => 'WE_ARE (TIMESPAN|also) STATUS( for)? COMMISSIONS',
        'G16' => 'WE_ARE STATUS for all costume, mascot and fursuit work TIMESPAN',
        'G17' => 'WE STATUS\.',
        'G18' => 'TIMESPAN COMMISSIONS status: fursuits STATUS',
        'G19' => '\[ COMMISSIONS[. ]+STATUS \]',
    ];
}
