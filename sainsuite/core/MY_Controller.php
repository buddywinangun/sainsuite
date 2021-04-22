<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * SainSuite
 *
 * Engine Management System
 *
 * @package     SainSuite
 * @copyright   Copyright (c) 2019-2020 Buddy Winangun, Eracik.
 * @copyright   Copyright (c) 2020-2021 SainTekno, SainSuite.
 * @link        https://github.com/saintekno/sainsuite
 * @filesource
 */
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Load Global Lang lines
        $this->lang->load_lines(APPPATH . '/language/*.php');
        
        // Load Addons
        include_once( APPPATH . 'core/MY_Addon.php' );
        MY_Addon::load( ADDONSPATH );

        // if is installed, setup is always loaded
        if ( $this->install_model->is_installed() ) 
        {
            // Load Model
            $this->load->model('options_model');

            // language is set on dashboard
            $this->options_model->defineLanguage();

            //init active addons
            MY_Addon::init('actives'); 

            // add events
            $this->events->do_action('do_app_init');
        }
        // Only for controller requiring installation
        elseif ( $this->uri->segment(1) === 'install' ) {
            MY_Addon::init('all');
        }
        
        // Checks system status
        if ( in_array( $this->uri->segment(1), $this->config->item('reserved_controllers') ) || $this->uri->segment(1) === null ) {
            // installation is required
            if (( in_array( $this->uri->segment(1), $this->config->item('controllers_requiring_installation') ) || $this->uri->segment(1) === null 
                ) && ! $this->install_model->is_installed() ) {
                redirect('install');
            }
        }
    }
}