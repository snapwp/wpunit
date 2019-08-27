<?php

namespace Snap\WPUnit\Drivers;

abstract class AbstractDriver
{
    /**
     * Return a fully initialized RemoteWebDriver.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    abstract public function start();

    /**
     * Stop the active RemoteWebDriver instance.
     */
    abstract public function stop();

    /**
     * Return if the current OS is determined to be Windows.
     *
     * @return bool
     */
    protected function is_windows()
    {
        return PHP_OS === 'WINNT' || \strpos(\php_uname(), 'Microsoft') !== false;
    }

    /**
     * Return if the current OS is determined to be Mac.
     *
     * @return bool
     */
    protected function is_mac()
    {
        return PHP_OS === 'Darwin';
    }
}