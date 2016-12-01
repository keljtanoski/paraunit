<?php


namespace Paraunit\Proxy\Coverage;

use SebastianBergmann\CodeCoverage\Driver\PHPDBG;

/**
 * Class FakeDriver
 * @package Paraunit\Proxy\Coverage
 */
class FakeDriver extends PHPDBG
{
    public function __construct()
    {
        // nope
    }

    public function start($determineUnusedAndDead = true)
    {
        throw new \RuntimeException('This is a fake implementation, it shouldn\'t be used!');
    }

    public function stop()
    {
        throw new \RuntimeException('This is a fake implementation, it shouldn\'t be used!');
    }
}