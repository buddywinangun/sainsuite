<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Racik
 *
 * Web App Starter CodeIgniter-based
 *
 * @package   Racik
 * @copyright Copyright (c) 2019
 * @since     Version 0.1
 * @link      https://github.com/boedwinangun/racik
 * @filesource
 */

/**
 * Application Hooks
 *
 * A set of methods used for the CodeIgniter hooks.
 * @link https://ellislab.com/codeigniter/user-guide/general/hooks.html
 *
 * @package Racik\Hooks\App_hooks
 */

class App_hooks
{
    /** @var array List of pages which bypass the Site Offline page. */
    protected $allowOffline = array(
        '/users/login',
        '/users/logout',
    );

    protected $isInstalled = false;

    /**
     * @var object The CodeIgniter core object.
     */
    private $ci;

    /**
     * @var array List of pages for which the URL-save/prep hooks are not run.
     */
    private $ignore_pages = array(
        '/users/login',
        '/users/logout',
        '/users/register',
        '/users/forgot_password',
        '/users/activate',
        '/users/resend_activation',
        '/images',
    );

    //--------------------------------------------------------------------------

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->ci =& get_instance();

        if (is_object($this->ci)) {
            $this->isInstalled = $this->ci->config->item('racik.installed');
            if (! $this->isInstalled) {
                // Is racik installed?
                $this->ci->load->library('installer');
                $this->isInstalled = $this->ci->installer->is_installed();
            }
        }
    }

    /**
     * Check the online/offline status of the site.
     *
     * Called by the "post_controller_constructor" hook.
     *
     * @return void
     */
    public function checkSiteStatus()
    {
        if (! isset($this->ci->load)) {
            return;
        }

        // If the settings lib is not available, load it.
        if (! isset($this->ci->settings_lib)) {
            $this->ci->load->library('settings/settings_lib');
        }

        if ($this->ci->settings_lib->item('site.status') != 0) {
            return;
        }

        if (! class_exists('Auth', false)) {
            $this->ci->load->library('users/auth');
        }

        if (! $this->ci->auth->has_permission('Site.Signin.Offline')
            && ! $this->ruriInArray($this->allowOffline)
        ) {
            $offlineReason = $this->ci->settings_lib->item('site.offline_reason');
            include(APPPATH . 'errors/offline.php');
            die();
        }
    }

    /**
     * Stores the name of the current uri in the session as 'previous_page'.
     * This allows redirects to take us back to the previous page without
     * relying on inconsistent browser support or spoofing.
     *
     * Called by the "post_controller" hook.
     *
     * @return void
     */
    public function prepRedirect()
    {
        if (! class_exists('CI_Session', false)) {
            $this->ci->load->library('session');
        }

        if (! $this->ruriInArray($this->ignore_pages)) {
            $this->ci->session->set_userdata('previous_page', current_url());
        }
    }

    /**
     * Store the requested page in the session data so we can use it
     * after the user logs in.
     *
     * Called by the "pre_controller" hook.
     *
     * @return void
     */
    public function saveRequested()
    {
        if (! $this->isInstalled) {
            return;
        }

        // If the CI_Session class is not loaded, this might be a controller that
        // doesn't extend any of racik's controllers. In that case, try to do
        // this the old fashioned way and add it straight to the session.

        if (! class_exists('CI_Session', false)) {
            if (is_object(get_instance())) {
                // If an instance is available, just load the session lib.
                $this->ci->load->library('session');
            } elseif (get_instance() === null) {
                // If an instance is not available...
                // Try to grab the REQUEST_URI since this will work in most cases.
                $uri = empty($_SERVER['REQUEST_URI']) ? null : $_SERVER['REQUEST_URI'];
                if (empty($uri)) {
                    // Try to get the current URL through PATH INFO.
                    $path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
                    if (trim($path, '/') != '' && $path != '/' . SELF) {
                        $uri = $path;
                    }
                }

                if (empty($uri)) {
                    // Finally, try the query string.
                    $path =  isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
                    if (trim($path, '/') != '') {
                        $uri = $path;
                    }
                }

                // Set the variable in the session and return.
                $_SESSION['requested_page'] = $uri;
                return;
            }
        }

        // Either the session library was available all along or it has been loaded,
        // so determine whether the current URL is in the ignore_pages array and,
        // if it is not, set it as the requested page in the session.

        if (! $this->ruriInArray($this->ignore_pages)) {
            $this->ci->session->set_userdata('requested_page', current_url());
        }
    }

    protected function ruriInArray(array $ruriArray)
    {
        // Output of uri->ruri_string() is considerably different in CI 3 when using
        // the RP_Router, so the following normalizes the output for the comparison
        // with $this->ignore_pages.
        $ruriString = '/' . ltrim(
            str_replace($this->ci->router->directory, '', $this->ci->uri->ruri_string()),
            '/'
        );
        return in_array($ruriString, $ruriArray);
    }
}