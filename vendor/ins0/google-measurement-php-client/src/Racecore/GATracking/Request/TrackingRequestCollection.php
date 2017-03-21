<?php

namespace Racecore\GATracking\Request;

use Racecore\GATracking\Tracking;

class TrackingRequestCollection implements \Iterator
{
    private $requestHolder = array();

    public function add(TrackingRequest $request)
    {
        $this->requestHolder[] = $request;
    }

    public function addElements($array)
    {
        foreach ($array as $element) {
            $this->add($element);
        }
    }

    public function rewind()
    {
        reset($this->requestHolder);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->requestHolder);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->requestHolder);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return next($this->requestHolder);
    }

    public function prev()
    {
        return prev($this->requestHolder);
    }

    public function getIterator()
    {
        $this->rewind();
        return $this;
    }

    public function valid()
    {
        return (bool) $this->current();
    }

    public function count()
    {
        return count($this->requestHolder);
    }

    public function indexOf($element)
    {
        return array_search($element, $this->requestHolder);
    }

    public function removeElement($element)
    {
        $key = $this->indexOf($element);
        if ($key) {
            unset($this->requestHolder[$key]);
            return true;
        }
        return false;
    }
}
