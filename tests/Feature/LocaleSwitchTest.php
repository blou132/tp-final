<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    public function test_locale_switch_sets_session_and_redirects_back_to_clean_url(): void
    {
        $response = $this
            ->withHeader('referer', 'http://localhost/dashboard?v=1776539160&status=open')
            ->get(route('locale.switch', ['locale' => 'en']));

        $response->assertRedirect('/dashboard?status=open');
        $response->assertSessionHas('locale', 'en');
    }

    public function test_locale_switch_rejects_unsupported_locale(): void
    {
        $response = $this->get('/locale/es');

        $response->assertNotFound();
    }
}
