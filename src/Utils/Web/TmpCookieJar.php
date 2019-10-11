<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Regexp\RegexpMatchException;
use App\Utils\Regexp\Utils as Regexp;
use Symfony\Component\Filesystem\Filesystem;

class TmpCookieJar implements CookieJarInterface
{
    private const NAME_PLACEHOLDER = '__COOKIE_NAME__';
    private const COOKIE_VALUE_REGEXP = '#^\S+\t(?:FALSE|TRUE)\t\S+\t(?:FALSE|TRUE)\t\d+\t'.self::NAME_PLACEHOLDER.'\t(?<value>\S+)$#mi';

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

    /**
     * @param string $cookieName
     *
     * @return string
     *
     * @throws HttpClientException
     */
    public function getValue(string $cookieName): string
    {
        $regexp = str_replace(self::NAME_PLACEHOLDER, preg_quote($cookieName), self::COOKIE_VALUE_REGEXP);

        try {
            return urldecode(Regexp::requireMatch($regexp, $this->getFileContents())['value']);
        } catch (RegexpMatchException $e) {
            throw new HttpClientException("Cookie '$cookieName' not present in jar", 0, $e);
        }
    }

    private function getFileContents(): string
    {
        return file_get_contents($this->filePath);
    }
}
