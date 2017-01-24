<?php

namespace Paraunit\Parser\JSON;

/**
 * Class UnknownResultParser
 * @package Paraunit\Parser\JSON
 */
class UnknownResultParser extends GenericParser
{
    /**
     * @param \stdClass $log
     * @return bool
     */
    protected function logMatches(\stdClass $log)
    {
        // catch 'em all!
        return true;
    }
}