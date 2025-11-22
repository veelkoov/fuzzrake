<?php

declare(strict_types=1);

namespace App\Tracking\TextProcessing;

use App\Tracking\ContextLogger;
use App\Tracking\Data\AnalysisInput;
use App\Tracking\Patterns\Patterns;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Lists\StringList;

class Preprocessor
{
    private const int MAX_ANALYSED_SIZE_CHARACTERS = 1 * 1024 * 1024; // ~= 1 MiB (+multibyte characters)

    private readonly ContextLogger $logger;

    public function __construct(
        #[Autowire(service: 'monolog.logger.tracking')]
        LoggerInterface $logger,
        private readonly Patterns $patterns,
    ) {
        $this->logger = new ContextLogger($logger);
    }

    public function getPreprocessedContent(AnalysisInput $input): string
    {
        $this->logger->resetContextFor($input);

        return $this->getWithLengthLimit($input)
            |> $input->url->getStrategy()->filterContents(...)
            |> strtolower(...)
            |> $this->patterns->cleaners->do(...)
            |> (fn ($contents) => $this->replaceCreatorAliases($contents, $input->creatorAliases))
            |> $this->patterns->falsePositives->do(...);
    }

    private function replaceCreatorAliases(string $input, StringList $aliases): string
    {
        $result = $input;

        foreach ($aliases as $alias) {
            $alias = strtolower($alias);

            $result = str_replace($alias, 'CREATOR_NAME', $result);

            if (mb_strlen($alias) > 2 && str_ends_with($alias, 's')) {
                $result = str_replace(substr($alias, 0, -1)."'s", 'CREATOR_NAME', $result);
            }
        }

        return $result;
    }

    private function getWithLengthLimit(AnalysisInput $input): string
    {
        $length = mb_strlen($input->contents);

        if ($length <= self::MAX_ANALYSED_SIZE_CHARACTERS) {
            return $input->contents;
        }

        $this->logger->info("Contents too long, truncating $length ---> ".$this::MAX_ANALYSED_SIZE_CHARACTERS.'.');

        return mb_substr($input->contents, 0, self::MAX_ANALYSED_SIZE_CHARACTERS);
    }
}
