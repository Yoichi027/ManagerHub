<?php

declare(strict_types=1);

namespace App\Tests\Web;

use App\Tests\Support\WebTester;

final class NotFoundCest
{
    public function unknownPageShowsTheNotFoundPage(WebTester $I): void
    {
        $I->amOnPage('/not-a-real-page');

        $I->seeResponseCodeIs(404);
        $I->see('This page is offside.', 'h1');
        $I->seeLink('Back to home');
    }
}
