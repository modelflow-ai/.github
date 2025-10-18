<?php

declare(strict_types=1);

/*
 * This file is part of the Modelflow AI package.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Reference;

if (!\function_exists('Symfony\Component\DependencyInjection\Loader\Configurator\service')) {
    /**
     * Creates a service reference.
     */
    function service(string $serviceId): Reference
    {
        return new Reference($serviceId);
    }
}

if (!\function_exists('Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator')) {
    /**
     * Creates a tagged iterator argument.
     */
    function tagged_iterator(string $tag): TaggedIteratorArgument
    {
        return new TaggedIteratorArgument($tag);
    }
}

if (!\function_exists('Symfony\Component\DependencyInjection\Loader\Configurator\service_closure')) {
    /**
     * Creates a service closure argument.
     */
    function service_closure(string $serviceId): ServiceClosureArgument
    {
        return new ServiceClosureArgument(new Reference($serviceId));
    }
}
