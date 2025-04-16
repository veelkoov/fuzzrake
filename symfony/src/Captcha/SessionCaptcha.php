<?php

declare(strict_types=1);

namespace App\Captcha;

use App\Captcha\Challenge\Challenge;
use LogicException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionCaptcha
{
    private const string SESSION_KEY_CHALLENGE = 'challenge';
    private const string SESSION_KEY_SOLVED = 'solved';

    private Challenge $challenge;
    private bool $isSolved;

    public function __construct(
        private readonly SessionInterface $session,
        private readonly ChallengeProvider $challengeProvider,
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
    public function hasBeenSolved(Request $request, FormInterface $form): bool
    {
        if ($this->isSolved) {
            return true;
        }

        if (!$request->isMethod(Request::METHOD_POST)) {
            return false;
        }

        $solved = true;

        foreach ($this->challenge->questions as $question) {
            foreach ($question->options as $option) {
                $checkedOrNull = $request->request->get($option->id);

                if ((null !== $checkedOrNull) !== $option->correct) {
                    $solved = false;
                    break 2;
                }
            }
        }

        if ($solved) {
            $this->setSolved(true);
        } else {
            $this->getCaptchaField($form)->addError(new FormError('Failed captcha challenge. Please try again.'));

            $this->setSolved(false);
            $this->setNewChallenge();
        }

        return $solved;
    }

    public function getChallenge(): Challenge
    {
        return $this->challenge;
    }

    /**
     * @param FormInterface<covariant mixed> $form
     *
     * @return FormInterface<mixed>
     */
    private function getCaptchaField(FormInterface $form): FormInterface
    {
        $result = null;

        foreach ($form as $child) {
            if ($child->getConfig()->getType()->getInnerType() instanceof CaptchaType) {
                if (null !== $result) {
                    throw new LogicException('More than one captcha fields in form.');
                }

                $result = $child;
            }
        }

        if (null === $result) {
            throw new LogicException('No captcha field in form.');
        }

        return $result;
    }

    private function setNewChallenge(): void
    {
        $this->challenge = $this->challengeProvider->getNewChallenge();
        $this->session->set(self::SESSION_KEY_CHALLENGE, $this->challenge);
    }

    private function setSolved(bool $isSolved): void
    {
        $this->isSolved = $isSolved;
        $this->session->set(self::SESSION_KEY_SOLVED, $isSolved);
    }

    public function isSolved(): bool
    {
        return $this->isSolved;
    }
}
