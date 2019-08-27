<?php

namespace Snap\WPUnit\Drivers;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Component\Process\Process;

/**
 * Manages the process which starts/stops the chromedriver.
 */
class Chrome extends AbstractDriver
{
    /**
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    private $driver;

    /**
     * @var \Facebook\WebDriver\Chrome\ChromeOptions
     */
    private $chrome_options;

    /**
     * @var array
     */
    private $arguments = [
        'disable-gpu' => null,
        'headless' => null,
        'window-size' => '1920,1080',
    ];

    /**
     * @var \Symfony\Component\Process\Process
     */
    private static $process;

    /**
     * Chrome constructor.
     */
    public function __construct()
    {
        $this->chrome_options = new ChromeOptions;
    }

    /**
     * Add an additional command line argument for the chromedriver.
     *
     * @param string $arg   The argument.
     * @param string $value The value to assign to this argument.
     * @see https://peter.sh/experiments/chromium-command-line-switches/
     */
    public function addArgument(string $arg, string $value = null): void
    {
        if (!$this->driver) {
            $this->arguments[$arg] = $value;
        }
    }

    /**
     * Return the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    public function start(): RemoteWebDriver
    {
        if ($this->driver) {
            return $this->driver;
        }

        $this->startProcess();

        $this->chrome_options->addArguments($this->parseArguments());

        $this->driver = RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $this->chrome_options
            )
        );

        return $this->driver;
    }

    /**
     * Stop the currently active RemoteWebDriver.
     */
    public function stop(): void
    {
        $this->driver->quit();
        $this->stopProcess();
    }

    public function getProcess()
    {
        return static::$process;
    }

    /**
     * Start up the Symfony Process which enables the Selenium server and browser driver.
     */
    private function startProcess(): void
    {
        if (static::$process) {
            return;
        }

        static::$process = new Process([$this->locateBinary(), '']);
        static::$process->start();
    }

    /**
     * Stop the Symfony Process.
     */
    private function stopProcess(): void
    {
        if (static::$process) {
            static::$process->stop();
        }
    }

    /**
     * Attempt to locate the chromedriver binary path.
     *
     * @return string
     * @throws \Exception If an executable chromedriver could not be found.
     */
    private function locateBinary(): string
    {
        if ($this->is_windows()) {
            return realpath(__DIR__ . '/../bin/windows/chromedriver.exe');
        }

        $binary_path = $this->is_mac()
            ? realpath(__DIR__ . '/../bin//mac/chromedriver')
            : realpath(__DIR__ . '/../bin/linux/chromedriver');

        if ($binary_path === false) {
            throw new \Exception('WPUnit: Could not locate the chromedriver executable file');
        }

        return $binary_path;
    }

    /**
     * Parse the $arguments into CLI commands.
     *
     * @return array
     */
    private function parseArguments(): array
    {
        $arguments = [];

        foreach ($this->arguments as $arg => $value) {
            if ($value === null) {
                $arguments[] = "--$arg";
                continue;
            }

            $arguments[] = "--${arg}=" . (string)$value;
        }

        return $arguments;
    }
}