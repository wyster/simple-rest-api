<?php declare(strict_types=1);

namespace Controller;

use App\Entity;
use App\Model;
use Exception;
use Faker\Factory as Faker;
use Fig\Http\Message\StatusCodeInterface;
use Money\Money;

use FunctionalTester;

class ProductControllerCest
{
    public function tryFetchAll(FunctionalTester $I): void
    {
        $faker = Faker::create(FAKER_LANG);

        $I->amOnPage('/product');
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_OK);
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $I->assertSame('[]', $content);

        $product = new Entity\Product();
        $product->setTitle($faker->text());
        $product->setPrice(Money::USD(10000));

        $model = $I->grabServiceFromContainer(Model\Product::class);
        if (!$model->create($product)) {
            throw new Exception('Row not created');
        }

        $I->amOnPage('/product');
        $I->seeResponseCodeIs(StatusCodeInterface::STATUS_OK);
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->canSeeResponseIsJson();
        $content = $I->grabPageSource();
        $data = json_decode($content, true);
        $I->assertCount(1, $data);
    }
}
