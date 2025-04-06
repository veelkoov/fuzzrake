<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Veelkoov\Debris\Base\DMap;
use Veelkoov\Debris\StringBoolMap;

class FurryCaptcha
{
    private const array ANIMALS = [ // @phpstan-ignore classConstant.unused (FIXME)
        'wolf' => '🐺',
        'dog' => '🐶',
        'cat' => '🐱',
        'tiger' => '🐅',
        'mouse' => '🐭',
        'hedgehog' => '🦔',
        'penguin' => '🐧',
        'zebra' => '🦓',
        'fish' => '🐟',
        'otter' => '🦦',
        'beaver' => '🦫',
        'hawk' => '🦅',
        'sloth' => '🦥',
        'swan' => '🦢',
        'duck' => '🦆',
    ];

    private const array QUESTIONS = [
        'can fly' => [
            'wolf' => false,
            'dog' => false,
            'cat' => false,
            'tiger' => false,
            'mouse' => false,
            'hedgehog' => false,
            'penguin' => false,
            'zebra' => false,
            'fish' => false,
            'otter' => false,
            'beaver' => false,
            'hawk' => true,
            'sloth' => false,
            'swan' => true,
            'duck' => true,
        ],
        'are a bird' => [
            'wolf' => false,
            'dog' => false,
            'cat' => false,
            'tiger' => false,
            'mouse' => false,
            'hedgehog' => false,
            'penguin' => true,
            'zebra' => false,
            'fish' => false,
            'otter' => false,
            'beaver' => false,
            'hawk' => true,
            'sloth' => false,
            'swan' => true,
            'duck' => true,
        ],
        'have striped fur' => [
            'wolf' => false,
            'dog' => false,
            'cat' => false,
            'tiger' => true,
            'mouse' => false,
            'hedgehog' => false,
            'penguin' => false,
            'zebra' => true,
            'fish' => false,
            'otter' => false,
            'beaver' => false,
            'hawk' => false,
            'sloth' => false,
            'swan' => false,
            'duck' => false,
        ],
    ];

    public function getCurrentChallenge(SessionInterface $session): CaptchaChallenge
    {
        // Pick two random questions
        $rawQuestions = $this->getQuestionsData()->shuffle()->slice(0, 2);
        $questions = [];

        foreach ($rawQuestions as $rawQuestion => $answers) {
            $firstTrueAnswer = (string) $answers->filterValues(fn (bool $value) => $value === true)->shuffle()->slice(0, 1)->single()->key; // FIXME: Implement random

            $selectedAnswers = $answers->filterKeys(fn (string $key) => $key !== $firstTrueAnswer)->shuffle()->slice(0, 3);
            $selectedAnswers->set($firstTrueAnswer, true);
            $selectedAnswers = $selectedAnswers->shuffle();

            $answers = CaptchaChallengeAnswerSet::mapFrom(
                $selectedAnswers,
                fn (bool $value, string $key) => new CaptchaChallengeAnswer($key, self::ANIMALS[$key], $value),
            );

            $questions[] = new CaptchaChallengeQuestion(
                $rawQuestion,
                $answers->freeze(),
            );
        }

        return new CaptchaChallenge($questions);
    }

    /**
     * @return DMap<covariant string, StringBoolMap>
     */
    private function getQuestionsData(): DMap
    {
        return DMap::mapFrom(self::QUESTIONS, fn ($value, string $key): array => [$key, new StringBoolMap($value)]);
    }
}
