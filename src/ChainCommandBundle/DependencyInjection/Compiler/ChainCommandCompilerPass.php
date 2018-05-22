<?php

namespace ChainCommandBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ChainCommandCompilerPass.
 */
class ChainCommandCompilerPass implements CompilerPassInterface
{
    const DEFAULT_MASTER = false;
    const DEFAULT_CHANNEL = 'default';
    const DEFAULT_WEIGHT = 10;

    /**
     * Register commands with tagged "chaincommandbundle.command" for chain class.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $r = 1;
        $i = 1;
        $q = 1;
        $definition = $container->findDefinition(
            'chaincommandbundle.chain'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'chaincommandbundle.command'
        );

        $tagsLabels = ['master', 'channel', 'weight'];
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {

                /*
                 * Set default values for command registering.
                 */
                foreach ($tagsLabels as $label) {
                    $attributes[$label] = isset($attributes[$label])
                        ? $attributes[$label]
                        : constant('self::DEFAULT_'.strtoupper($label));
                }

                $definition->addMethodCall(
                    'registerCommand',
                    [
                        new Reference($id),
                        $attributes['master'],
                        $attributes['channel'],
                        $attributes['weight'],
                    ]
                );
            }
        }
    }
}
