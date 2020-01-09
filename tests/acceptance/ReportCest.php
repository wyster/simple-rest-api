<?php declare(strict_types=1);

use Fig\Http\Message\StatusCodeInterface;

final class ReportCest
{
    public function tryReport(AcceptanceTester $I): void
    {
        $I->amOnPage('/c3/report/clear');
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_OK);
    }
}
