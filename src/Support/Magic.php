<?php

namespace Sribna\Updater\Support;

use BadMethodCallException;

/**
 * Trait Magic
 * @package Sribna\Updater\Support
 */
trait Magic
{

    /**
     * @param $method
     * @param $params
     * @return $this
     */
    public function __call($method, $params)
    {
        $var = lcfirst(substr($method, 3));

        if (strncasecmp($method, "get", 3) === 0) {
            return $this->$var;
        }
        if (strncasecmp($method, "set", 3) === 0) {
            $this->$var = $params[0];
            return $this;
        }

        throw new BadMethodCallException("Method $method does not exist in class " . get_class($this));
    }

}
