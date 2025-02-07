<?php

namespace Flagception\Model;

use Flagception\Exception\AlreadyDefinedException;
use Serializable;

/**
 * Class Context
 *
 * @author Michel Chowanski <michel.chowanski@bestit-online.de>
 * @package Flagception\Model
 */
class Context implements Serializable
{
    /**
     * Storage for all context values
     *
     * @var array<string, mixed>
     */
    private $storage = [];

    /**
     * Context constructor
     *
     * @param array<string, mixed> $storage
     * @throws AlreadyDefinedException
     */
    public function __construct(array $storage = [])
    {
        foreach ($storage as $name => $value) {
            $this->add($name, $value);
        }
    }

    /**
     * Add a context value. The key must be unique and cannot be replaced
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     * @throws AlreadyDefinedException
     */
    public function add(string $name, $value)
    {
        if (array_key_exists($name, $this->storage)) {
            throw new AlreadyDefinedException(sprintf('Context value with key `%s` already defined', $name));
        }

        $this->storage[$name] = $value;
    }

    /**
     * Replace a context value
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function replace(string $name, $value)
    {
        $this->storage[$name] = $value;
    }

    /**
     * Get context value of given string or default value
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return array_key_exists($name, $this->storage) ? $this->storage[$name] : $default;
    }

    /**
     * Get all context values (key => value pairs)
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->storage;
    }

    /**
     * Has given context value
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->storage);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->storage);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $this->storage = unserialize($serialized);
    }

    public function __serialize()
    {
        return [$this->serialize()];
    }

    public function __unserialize(array $data)
    {
        $this->unserialize($data[0]);
    }
}
