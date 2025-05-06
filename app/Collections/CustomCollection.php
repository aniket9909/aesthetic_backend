<?php

namespace App\Collections;

use Illuminate\Support\Collection;

class CustomCollection extends Collection
{
    // Override ArrayAccess methods to make them compatible with PHP 8.1
    
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return parent::offsetExists($key);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return parent::offsetGet($key);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        parent::offsetSet($key, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        parent::offsetUnset($key);
    }
}
