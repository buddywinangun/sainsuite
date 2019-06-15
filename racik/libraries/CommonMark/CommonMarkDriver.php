<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Racik
 *
 * Web App Starter CodeIgniter-based
 *
 * @package   Racik
 * @copyright Copyright (c) 2019
 * @since     Version 0.8
 * @link      https://github.com/boedwinangun/racik
 * @filesource
 */


/**
 * Racik CommonMarkDriver
 *
 * The base class for CommonMark drivers/adapters to allow Racik applications
 * to use a common interface to interact with third-party libraries to convert
 * CommonMark text into HTML text.
 */
abstract class CommonMarkDriver
{
    /** @var object The instance of the underlying library. */
    protected $converter = null;

    /** @var string The class to instantiate and load into $this->converter. */
    protected $converterLib = '';

    /** @var array The name(s) of the file(s) to load the library manually. */
    protected $files = array();

    /** @var array Paths to search when attempting to load a library manually. */
    protected $paths = array();

    /**
     * Convert CommonMark text to HTML.
     *
     * This method must be implemented by the driver/adapter.
     *
     * @param string $text CommonMark text to convert.
     *
     * @return string HTML text.
     */
    abstract protected function toHtml($text);

    /**
     * The driver/adapter may overload this method to perform initialization (load
     * the underlying library) itself.
     *
     * @return bool True if the initialization was performed successfully, else
     * false.
     */
    protected function init()
    {
        return false;
    }

    /**
     * The interface used by the CommonMark library to convert CommonMark text to
     * HTML. If the converter has not been loaded, attempts to load it.
     *
     * @param string $text CommonMark text to convert.
     *
     * @return string HTML text or null if there was an error.
     */
    public function convert($text)
    {
        // If the converter has not been loaded, attempt to load it.
        if ($this->converter === null) {
            // Return null if the converter fails to load.
            if ($this->loadLibrary() === false) {
                return null;
            }

            // If the converter has not been instantiated, yet, do it.
            $this->converter = $this->converter === null ? new $this->converterLib() : $this->converter;
        }

        return $this->toHtml($text);
    }

    /**
     * Load the underlying library used by the driver/adapter.
     *
     * @return bool True if the library was loaded successfully, else false.
     */
    private function loadLibrary()
    {
        // Allow the driver to perform the initialization itself.
        if ($this->init()) {
            return true;
        }

        // If possible, allow composer to load the driver's library.
        if (get_instance()->config->item('composer_autoload') !== false) {
            if (class_exists($this->converterLib)) {
                return true;
            }
        }

        // Is there a list of paths and files to load?
        if (! empty($this->paths) && ! empty($this->files)) {
            foreach ($this->paths as $path) {
                if (file_exists("{$path}/{$this->files[0]}")) {
                    foreach ($this->files as $file) {
                        require_once("{$path}/{$file}");
                    }

                    return true;
                }
            }
        }

        // The driver's converter has not been loaded.
        log_message('error', 'CommonMarkDriver: Converter not found.');

        return false;
    }
}
