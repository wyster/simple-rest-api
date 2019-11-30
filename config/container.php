<?php declare(strict_types=1);

$builder = new DI\ContainerBuilder();

$builder->addDefinitions(require __DIR__ . '/definitions.php');
$builder->useAnnotations(false);
$builder->useAutowiring(true);

return $builder->build();
