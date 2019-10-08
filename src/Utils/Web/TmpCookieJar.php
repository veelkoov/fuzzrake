<?php

declare(strict_types=1);

namespace App\Utils\Web;

use Symfony\Component\Filesystem\Filesystem;

class TmpCookieJar implements CookieJarInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Filesystem
     */
    private $fs;

    public function __construct()
    {
        $this->fs = new Filesystem();
        $this->filePath = $this->fs->tempnam(sys_get_temp_dir(), 'FuzzrakeCookieJar');
    }

    public function __destruct()
    {
        $this->fs->remove($this->filePath);
    }

    public function setupFor($curlHandle): void
    {
        curl_setopt_array($curlHandle, [
            CURLOPT_COOKIEFILE => $this->filePath,
            CURLOPT_COOKIEJAR  => $this->filePath,
        ]);
    }
}
