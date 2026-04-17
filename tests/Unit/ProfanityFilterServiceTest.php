<?php

namespace Tests\Unit;

use App\Services\ProfanityFilterService;
use Tests\TestCase;

class ProfanityFilterServiceTest extends TestCase
{
    public function test_it_detects_blocked_words(): void
    {
        config()->set('profanity.words', ['badword']);

        $service = new ProfanityFilterService;

        $this->assertTrue($service->contains('This message has badword inside.'));
        $this->assertFalse($service->contains('This message is clean.'));
    }

    public function test_it_sanitizes_blocked_words(): void
    {
        config()->set('profanity.words', ['badword']);
        config()->set('profanity.replacement_char', '*');

        $service = new ProfanityFilterService;

        $sanitized = $service->sanitize('This badword should be hidden.');

        $this->assertSame('This ******* should be hidden.', $sanitized);
    }
}
