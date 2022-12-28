<?php

namespace CORS\Bundle\FriendlyCaptchaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register friendly-captcha-bundle form templates.
 */
class ResourceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->setParameter(
            'twig.form.resources',
            array_merge(
                array(
                    '@CORSFriendlyCaptcha/friendlycaptcha.html.twig',
                ),
                $container->getParameter('twig.form.resources')
            )
        );
    }
}