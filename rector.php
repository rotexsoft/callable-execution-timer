<?php
declare(strict_types=1);

use Rector\Set\ValueObject\SetList;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {

    // Define what rule sets will be applied
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::PHP_72);
    $containerConfigurator->import(SetList::PHP_73);
    //$containerConfigurator->import(SetList::PHP_74);
    //$containerConfigurator->import(SetList::PHP_80);
    $containerConfigurator->import(SetList::PSR_4);
    $containerConfigurator->import(SetList::TYPE_DECLARATION);
    
    // get parameters
    //$parameters = $containerConfigurator->parameters();
    //$parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_73);

    // register / unregister one or more rules
    $services = $containerConfigurator->services();
    
    (PHP_MAJOR_VERSION < 8) && $services->remove(Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector::class);
};
