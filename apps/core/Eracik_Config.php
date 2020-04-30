<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Eracik_Config extends CI_Config
{
    public function __construct()
    {
        parent::__construct();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Build URI string
	 *
	 * @used-by	CI_Config::site_url()
	 * @used-by	CI_Config::base_url()
	 *
	 * @param	string|string[]	$uri	URI string or an array of segments
	 * @return	string
	 */
	protected function _uri_string($uri)
	{
		if ($this->item('enable_query_strings') === FALSE)
		{
			is_array($uri) && $uri = implode('/', array_filter( $uri ) );
			return ltrim($uri, '/');
		}
		elseif (is_array($uri))
		{
			return http_build_query($uri);
		}

		return $uri;
	}
}