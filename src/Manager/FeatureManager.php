<?php

namespace Flagception\Manager;

use Flagception\Activator\FeatureActivatorInterface;
use Flagception\Decorator\ContextDecoratorInterface;
use Flagception\Model\Context;

/**
 * Class FeatureManager
 *
 * @author Michel Chowanski <michel.chowanski@bestit-online.de>
 * @package Flagception\Manager
 */
class FeatureManager implements FeatureManagerInterface
{
    /**
     * The feature activator
     *
     * @var FeatureActivatorInterface
     */
    private $activator;

    /**
     * The context decorator
     *
     * @var ContextDecoratorInterface|null
     */
    private $decorator;

    /**
     * FeatureManager constructor.
     *
     * @param FeatureActivatorInterface $activator
     * @param ContextDecoratorInterface|null $decorator
     */
    public function __construct(FeatureActivatorInterface $activator, ?ContextDecoratorInterface $decorator = null)
    {
        $this->activator = $activator;
        $this->decorator = $decorator;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive(string $name, ?Context $context = null): bool
    {
        if ($context === null) {
            $context = new Context();
        }

        $context->replace('_feature', $name);

        if ($this->decorator !== null) {
            $context = $this->decorator->decorate($context);
        }

        return $this->activator->isActive($name, $context);
    }
}
