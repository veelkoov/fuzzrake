<?php

declare(strict_types=1);

namespace App\Captcha;

use App\Captcha\Challenge\Challenge;
use App\Captcha\Challenge\Question;
use App\Captcha\Challenge\QuestionOption;
use App\Captcha\Form\StandaloneCaptchaType;
use Override;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Veelkoov\Debris\Base\DList;
use Veelkoov\Debris\Base\DMap;
use Veelkoov\Debris\StringBoolMap;

class CaptchaService implements CaptchaProvider
{
    private const int QUESTIONS_PER_CHALLENGE = 2;
    private const int OPTIONS_PER_QUESTION = 4;

    private const array ANIMALS = [ // TODO: To YAML
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

    private const array QUESTIONS = [ // TODO: To YAML
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

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function getCaptcha(SessionInterface $session): Captcha
    {
        return new Captcha($session, $this);
    }

    /**
     * @return FormInterface<covariant mixed>
     */
    public function getStandaloneForm(): FormInterface
    {
        return $this->formFactory->create(StandaloneCaptchaType::class);
    }

    #[Override]
    public function getNewChallenge(): Challenge
    {
        // Pick X random questions
        $rawQuestions = $this->getQuestionsData()->shuffle()->slice(0, self::QUESTIONS_PER_CHALLENGE);
        $questions = [];

        foreach ($rawQuestions as $rawQuestion => $answers) {
            $firstTrueAnswer = (string) $answers->filterValues(static fn (bool $value) => true === $value)
                ->shuffle()->slice(0, 1)->single()->key; // FIXME: Implement random

            // FIXME: Should have first false answer as well

            $selectedAnswers = $answers->filterKeys(static fn (string $key) => $key !== $firstTrueAnswer)
                ->shuffle()->slice(0, self::OPTIONS_PER_QUESTION - 1);
            $selectedAnswers->set($firstTrueAnswer, true);
            $selectedAnswers = $selectedAnswers->shuffle();

            $options = DList::mapFrom(
                $selectedAnswers,
                fn (bool $value, string $key) => new QuestionOption($key, self::ANIMALS[$key], $value),
            )->getValuesArray();

            $questions[] = new Question($rawQuestion, $options);
        }

        return new Challenge($questions);
    }

    /**
     * @return DMap<covariant string, StringBoolMap>
     */
    private function getQuestionsData(): DMap
    {
        return DMap::mapFrom(self::QUESTIONS, fn ($value, string $key): array => [$key, new StringBoolMap($value)]);
    }
}
