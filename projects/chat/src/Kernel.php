<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function process(ContainerBuilder $container): void
    {
        $adapters = $container->getParameter('modelflow_ai.adapters');

        $models = [];
        foreach ($adapters as $key => $adapter) {
            $models[$adapter['model']] = $key;
        }

        $def = $container->getDefinition('twig');
        $def->addMethodCall('addGlobal', ['MODELS', $models]);
    }
}
