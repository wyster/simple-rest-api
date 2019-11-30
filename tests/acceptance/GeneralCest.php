<?php declare(strict_types=1);

class GeneralCest
{
    public function tryToTest(AcceptanceTester $I)
    {
        $I->amOnPage('/hello/world');
        $I->seeResponseCodeIs(200);
        $I->see('Hello world');
    }
}
