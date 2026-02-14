<?php

declare(strict_types=1);

namespace App\Utils\Traits;

use Exception;

trait UtilityClass
{
    /**
     * @throws Exception
     */
    final public function __construct()
    {
        /*
         * Yer a utility, Harry
         * https://www.reddit.com/r/BossFights/comments/9wbkq7/you_are_a_hazard_harry/
         */
        throw new Exception(static::class.' is not supposed to be constructed');
    }
}
