<?php

declare(strict_types=1);

namespace App\Captcha;

use App\Captcha\Challenge\Challenge;
use App\Captcha\Challenge\Question;
use App\Captcha\Challenge\QuestionOption;
use App\Captcha\Form\StandaloneCaptchaType;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Veelkoov\Debris\Base\DList;
use Veelkoov\Debris\Base\DMap;
use Veelkoov\Debris\StringBoolMap;
use Veelkoov\Debris\StringStringMap;

class CaptchaService implements CaptchaProvider
{
    private readonly int $questionsPerChallenge;
    private readonly int $optionsPerQuestion;
    private readonly StringStringMap $animals;
    /**
     * @var DMap<covariant string, StringBoolMap>
     */
    private readonly DMap $questions;

    /**
     * @param array{
     *     questions_per_challenge: positive-int,
     *     options_per_question: positive-int,
     *     animals: array<string, string>,
     *     questions: array<string, array<string, bool>>,
     *   } $parameters
     */
    public function __construct(
        #[Autowire(param: 'captcha')] array $parameters,
        private readonly FormFactoryInterface $formFactory,
    ) {
        $this->questionsPerChallenge = $parameters['questions_per_challenge'];
        $this->optionsPerQuestion = $parameters['options_per_question'];
        $this->animals = new StringStringMap($parameters['animals'], frozen: true);
        $this->questions = DMap::mapFrom($parameters['questions'],
            static fn ($value, string $key): array => [$key, new StringBoolMap($value, frozen: true)])->freeze();
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
        $rawQuestions = $this->questions->shuffle()->slice(0, $this->questionsPerChallenge);

        $questions = [];

        foreach ($rawQuestions as $rawQuestion => $answers) {
            $firstTrueAnswer = $answers->filterValues(static fn (bool $value) => true === $value)->randomKey();
            $firstFalseAnswer = $answers->filterValues(static fn (bool $value) => false === $value)->randomKey();

            $selectedAnswers = $answers->minusKey($firstTrueAnswer, $firstFalseAnswer)
                ->shuffle()->slice(0, $this->optionsPerQuestion - 2);
            $selectedAnswers->set($firstTrueAnswer, true);
            $selectedAnswers->set($firstFalseAnswer, false);
            $selectedAnswers = $selectedAnswers->shuffle();

            $options = DList::mapFrom(
                $selectedAnswers,
                fn (bool $value, string $key) => new QuestionOption($key, $this->animals->get($key), $value),
            )->getValuesArray();

            $questions[] = new Question($rawQuestion, $options);
        }

        return new Challenge($questions);
    }
}
