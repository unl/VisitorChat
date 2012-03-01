<?php
class Savvy_ObjectProxy_Traversable extends Savvy_ObjectProxy implements Iterator
{

    function getIterator()
    {
        return $this->object;
    }

    function next()
    {
        $this->object->next();
    }

    function key()
    {
        return $this->object->key();
    }

    function valid()
    {
        return $this->object->valid();
    }

    function rewind()
    {
        $this->object->rewind();
    }

    function current()
    {
        return $this->filterVar($this->object->current());
    }
}