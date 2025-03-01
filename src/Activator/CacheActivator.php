<?php

namespace Flagception\Activator;

use DateInterval;
use Flagception\Model\Context;
use Psr\Cache\CacheItemPoolInterface as CachePool;

/**
 * Generic cache for activator
 *
 * @author Michel Chowanski <michel.chowanski@bestit-online.de>
 * @package Flagception\Activator
 */
class CacheActivator implements FeatureActivatorInterface
{
    /**
     * Cache key
     */
    public const CACHE_KEY = 'flagception';

    /**
     * The origin activator
     *
     * @var FeatureActivatorInterface
     */
    private $activator;

    /**
     * The cache pool
     *
     * @var CachePool
     */
    private $cachePool;

    /**
     * Time to live for cache items
     * Valid values are identical to \Psr\Cache\CacheItemInterface
     *
     * @var int|DateInterval|null
     */
    private $cacheTtl;

    /**
     * Short memory request cache
     *
     * @var bool[]
     */
    private $memory = [];

    /**
     * CacheActivator constructor.
     *
     * @param FeatureActivatorInterface $activator
     * @param CachePool $cachePool
     * @param int|DateInterval|null $cacheTtl
     */
    public function __construct(FeatureActivatorInterface $activator, CachePool $cachePool, $cacheTtl = 3600)
    {
        $this->activator = $activator;
        $this->cachePool = $cachePool;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * Get activator
     *
     * @return FeatureActivatorInterface
     */
    public function getActivator(): FeatureActivatorInterface
    {
        return $this->activator;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->activator->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function isActive(string $name, Context $context): bool
    {
        $hash = static::CACHE_KEY . '#' . $this->getName() . '#' . md5($name . '-' . $context->serialize());

        // Step 1: Try get from memory cache
        if (array_key_exists($hash, $this->memory)) {
            return $this->memory[$hash];
        }

        // Step 2: Try get from (optional) cache
        $cacheItem = null;
        if ($this->cachePool !== null) {
            $cacheItem = $this->cachePool->getItem($hash);

            if ($cacheItem->isHit()) {
                $this->memory[$hash] = $cacheItem->get();
                return $this->memory[$hash];
            }
        }

        // Step 3: Get from activators and save to cache
        $this->memory[$hash] = $this->activator->isActive($name, $context);

        // Write result to cache
        if ($this->cachePool !== null && $cacheItem !== null) {
            $cacheItem->set($this->memory[$hash]);
            $cacheItem->expiresAfter($this->cacheTtl);
            $this->cachePool->save($cacheItem);
        }

        return $this->memory[$hash];
    }
}
