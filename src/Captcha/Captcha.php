<?php

declare(strict_types=1);

namespace App\Captcha;

use App\Captcha\Challenge\Challenge;
use App\Captcha\Form\CaptchaType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Veelkoov\Debris\Base\DList;

class Captcha
{
    private const string SESSION_KEY_CHALLENGE = 'challenge';
    private const string SESSION_KEY_SOLVED = 'solved';

    private Challenge $challenge;
    private bool $isSolved;

    public function __construct(
        private readonly SessionInterface $session,
        private readonly CaptchaProvider $provider,
    ) {
        $isSolved = $this->session->get(self::SESSION_KEY_SOLVED);
        $this->isSolved = is_bool($isSolved) ? $isSolved : false;

        $challenge = $this->session->get(self::SESSION_KEY_CHALLENGE);

        if ($challenge instanceof Challenge) {
            $this->challenge = $challenge;
        } else {
            $this->setNewChallenge();
        }
    }

    /**
     * @param FormInterface<covariant mixed> $form
     */
    public function handleRequest(Request $request, FormInterface $form): self
    {
        if ($this->isSolved || !$request->isMethod(Request::METHOD_POST)) {
            return $this;
        }

        if (self::solvedInRequest($this->challenge, $request)) {
            $this->setSolved(true);
        } else {
            self::getCaptchaField($form)->addError(new FormError('Failed captcha challenge. Please try again.'));

            $this->setSolved(false);
            $this->setNewChallenge();
        }

        return $this;
    }

    private function setNewChallenge(): void
    {
        $this->challenge = $this->provider->getNewChallenge();
        $this->session->set(self::SESSION_KEY_CHALLENGE, $this->challenge);
    }

    private function setSolved(bool $isSolved): void
    {
        $this->isSolved = $isSolved;
        $this->session->set(self::SESSION_KEY_SOLVED, $isSolved);
    }

    public function getChallenge(): Challenge
    {
        return $this->challenge;
    }

    public function isSolved(): bool
    {
        return $this->isSolved;
    }

    private static function solvedInRequest(Challenge $challenge, Request $request): bool
    {
        foreach ($challenge->questions as $question) {
            foreach ($question->options as $option) {
                $checkedOrNull = $request->request->get($option->id);

                if ((null !== $checkedOrNull) !== $option->correct) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param FormInterface<covariant mixed> $form
     *
     * @return FormInterface<mixed>
     */
    private static function getCaptchaField(FormInterface $form): FormInterface
    {
        return new DList($form)->filter(self::isCaptchaType(...))->single();
    }

    /**
     * @param FormInterface<mixed> $form
     */
    private static function isCaptchaType(FormInterface $form): bool
    {
        return $form->getConfig()->getType()->getInnerType() instanceof CaptchaType;
    }
}
