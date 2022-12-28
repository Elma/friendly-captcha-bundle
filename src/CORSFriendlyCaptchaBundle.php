<?php

declare(strict_types=1);

namespace CORS\Bundle\FriendlyCaptchaBundle;

use CORS\Bundle\FriendlyCaptchaBundle\DependencyInjection\Compiler\ResourceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CORSFriendlyCaptchaBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ResourceCompilerPass());
    }
}