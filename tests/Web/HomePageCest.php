<?php

declare(strict_types=1);

namespace App\Tests\Web;

use App\Tests\Support\WebTester;

final class HomePageCest
{
    public function homePageIntroducesManagerHub(WebTester $I): void
    {
        $I->amOnPage('/');

        $I->seeResponseCodeIs(200);
        $I->see('Manager Hub');
        $I->see('Run every season with a clearer plan.', 'h1');
        $I->seeLink('Create your account');
    }
}
