<?php declare(strict_types=1);

namespace App\Db\Seeds;

use Faker\Factory as Faker;
use Phinx\Seed\AbstractSeed;

class Products extends AbstractSeed
{
    public function run(): void
    {
        $faker = Faker::create(FAKER_LANG);
        $data = [];
        for ($i = 0; $i < 20; $i++) {
            $data[] = [
                'title' => $faker->text(),
                'price' => $faker->randomNumber(5),
            ];
        }

        $this->table('product')->insert($data)->save();
    }
}
