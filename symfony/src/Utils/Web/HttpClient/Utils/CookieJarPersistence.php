<?php

declare(strict_types=1);

namespace App\Utils\Web\HttpClient\Utils;

use App\Utils\Json;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Veelkoov\Debris\Lists\StringList;

class CookieJarPersistence
{
    private readonly Filesystem $filesystem;

    public function __construct(
        private readonly string $cachePath,
        private readonly CookieJar $cookieJar,
    ) {
        $this->filesystem = new Filesystem();

        try {
            $cookieStrings = StringList::fromUnsafe(Json::decode($this->filesystem->readFile($this->cachePath)));

            foreach ($cookieStrings as $cookieString) {
                $this->cookieJar->set(Cookie::fromString($cookieString));
            }
        } catch (IOException|JsonException|InvalidArgumentException) {
            // Silently ignore, start with an empty jar.
        }
    }

    public function save(): void
    {
        $cookieStrings = new StringList();

        foreach ($this->cookieJar->all() as $cookie) {
            $cookieStrings->add((string) $cookie);
        }

        try {
            $this->filesystem->dumpFile($this->cachePath, Json::encode($cookieStrings, JSON_PRETTY_PRINT));
        } catch (JsonException $exception) {
            throw new RuntimeException(previous: $exception);
        }
    }
}
