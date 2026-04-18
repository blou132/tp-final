<?php

namespace Tests\Feature;

use Tests\TestCase;

class CleanUrlParametersTest extends TestCase
{
    public function test_it_removes_version_query_parameter_from_url(): void
    {
        $response = $this->get('/?v=1776539160');

        $response->assertRedirect('/');
    }

    public function test_it_removes_lang_query_parameter_and_preserves_other_filters(): void
    {
        $response = $this->get('/?lang=fr&page=2');

        $response->assertRedirect('/?page=2');
        $response->assertSessionHas('locale', 'fr');
    }
}
