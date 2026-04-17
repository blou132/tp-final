<?php

namespace App\Services;

class ProfanityFilterService
{
    /** @var array<int, string> */
    private array $blockedWords;

    public function __construct()
    {
        $this->blockedWords = config('profanity.words', []);
    }

    public function contains(string $text): bool
    {
        foreach ($this->blockedWords as $word) {
            if ($word === '') {
                continue;
            }

            $pattern = '/\\b'.preg_quote($word, '/').'\\b/iu';

            if (preg_match($pattern, $text) === 1) {
                return true;
            }
        }

        return false;
    }

    public function sanitize(string $text): string
    {
        $replacementChar = (string) config('profanity.replacement_char', '*');

        foreach ($this->blockedWords as $word) {
            if ($word === '') {
                continue;
            }

            $pattern = '/\\b'.preg_quote($word, '/').'\\b/iu';
            $replacement = str_repeat($replacementChar, mb_strlen($word));
            $text = (string) preg_replace($pattern, $replacement, $text);
        }

        return $text;
    }
}
