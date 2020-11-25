<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * SainSuite
 *
 * Engine Management System
 *
 * @package     SainSuite
 * @copyright   Copyright (c) 2019-2020 Buddy Winangun, Eracik.
 * @copyright   Copyright (c) 2020 SainTekno, SainSuite.
 * @link        https://github.com/saintekno/sainsuite
 * @filesource
 */

class MY_Addon extends CI_Model
{
    private static $addons;
    
    private static $actives = array();

    private static $config_addon = 'addon.json';

    private static $manifest_addon = 'manifest.json';

    private static $allowed_app_folders = array( 'controllers', 'models', 'libraries', 'helpers', 'config' );

    public function __construct()
    {
        parent::__construct();
        //Codeigniter : Write Less Do More
    }

    public function addon_view( $namespace, $view, $params = array(), $return = false )
    {
        return $this->load->addon_view( $namespace, $view, $params, $return );
    }
    /**
     * Init
     */
    public static function init($filter, $addon_namespace = null)
    {
        global $Options;
        
        if ($addon_namespace != null) 
        {
            $addons = self::get($addon_namespace);
            if (isset($addons[ 'application' ][ 'main' ])) 
            {
                if (is_file($init_file = $addons[ 'application' ][ 'main' ]) 
                    && $filter === 'unique') 
                {
                    include_once($init_file);
                    return; // after special init return;
                }
            }
        } 
        else {
            $addons = self::get();
        }

        foreach ( ( array ) $addons as $addon) 
        {
            $init_file = $addon[ 'application' ][ 'main' ];

            // Load every addon when on install mode
            if ( ! isset($init_file) && ! is_file($init_file) ) { 
                return;
            }

            if ($filter === 'all') {                    
                include_once($init_file);
            }

            if ($filter === 'actives') 
            {                    
                if (!isset($actives_addons)) {
                    $actives_addons = ( array ) @$Options[ 'actives_addons' ];
                }
    
                if (in_array(strtolower($addon[ 'application' ][ 'namespace' ]), $actives_addons)) 
                {
                    self::$actives[] = $addon[ 'application' ][ 'namespace' ];
                    include_once($init_file);
                }
            }
        }  
    }

	// --------------------------------------------------------------------

    /**
     * Get
     *
     * Get a addon using namespace provided as unique parameter
     */
    public static function get($namespace = null )
    {
        if ($namespace == null) {
            return self::$addons;
        }
        
        return isset(self::$addons[ $namespace ]) 
            ? self::$addons[ $namespace ] 
            : false; // if addon exists
    }

	// --------------------------------------------------------------------

    /**
     * Load addons
     */
    public static function load($addon_path, $deepness = 0 )
    {
        if ($deepness < 2) 
        { 
            // avoid multi-folder parsing
            $dir = opendir($addon_path);
            $config = array();

            // Looping currend folder
            while (false !== ($file = readdir($dir))) 
            {
                if (substr($file, -11) === self::$config_addon) {
                    $config = json_decode(file_get_contents($addon_path . '/' . $file), true);
                } 
                elseif (is_dir($addon_path . '/' . $file) && ! in_array($file, array( '.', '..' ))) {
                    self::load($addon_path . '/' . $file, $deepness + 1); // Only top folder are parsed
                }
            }

            // Adding Valid init file to addon array
            // only namespace is required for a addon to be valid
            if (isset($config[ 'application' ][ 'namespace' ])) 
            {
                $namespace = strtolower($config[ 'application' ][ 'namespace' ]);
                
                // Saving details
                self::$addons[ $namespace ] = $config;
                
                // Edit main file path
                if (isset($config[ 'application' ][ 'main' ])) {
                    self::$addons[ $namespace ][ 'application' ][ 'main' ] = $addon_path . '/' . self::$addons[ $namespace ][ 'application' ][ 'main' ];
                }
                
                self::$addons[ $namespace ][ 'application' ][ 'namespace' ] = strtolower(self::$addons[ $namespace ][ 'application' ][ 'namespace' ]);
            }

            // Check for language directory
            // addon namespace is used as text domain
            if (isset($config[ 'application' ][ 'language' ])) 
            {
                if (is_dir($language_path = ADDONSPATH . $namespace . '/' . $config[ 'application' ][ 'language' ])) 
                {
                    $text_domain = get_instance()->config->item('text_domain');
                    $text_domain[ $namespace ] = $language_path;
                    get_instance()->config->set_item('text_domain', $text_domain);
                }
            }
        }
    }

	// --------------------------------------------------------------------

    /**
     *  Run dependency checker
     *  @return void
     * 
     *  Jalankan dependency
     */
    public static function runDependency()
    {
        global $Options;

        $addons = self::get();
        if ( ! empty($addons) ) 
        {
            foreach( $addons as $addon ) 
            {
                if( in_array( $addon[ 'application' ][ 'namespace' ], ( array ) @$Options[ 'actives_addons' ] ) ) 
                {
                    self::checkDependency( $addon[ 'application' ][ 'namespace' ] );
                }
            }
        }
    }

	// --------------------------------------------------------------------

    /**
     *  Check dependency
     *  Checks whether a plugin dependency is installed. Otherwise that addon is disabled
     * 
     *  Cek addon dependency sudah terinstall, jika belum maka addon di disable
     */
    public static function checkDependency( $addon_namespace )
    {
        $addon = self::get( $addon_namespace );

        if( isset( $addon[ 'application' ][ 'dependencies' ] ) && count( @$addon[ 'application' ][ 'dependencies' ] ) > 0 ) 
        {
            $dependencies_data = $addon[ 'application' ][ 'dependencies' ];
            if( is_array( @$dependencies_data[ 'addon' ] ) ) 
            {
                $keys = array_keys( @$dependencies_data[ 'addon' ] );
                
                if( in_array( $keys[0], array( '@value' ) , true ) ) {
                    $dependencies = array( $dependencies_data[ 'addon' ] );
                } else {
                    $dependencies = $dependencies_data[ 'addon' ];
                }

                foreach( $dependencies as $dependency ) 
                {
                    if( ! self::is_active( $dependency[ '@attributes' ][ 'namespace' ], true ) ) 
                    {
                        get_instance()->notice->push_notice(
                            sprintf(
                                __( '<strong>%s</strong> has been disabled. This addon require <strong>%s</strong> to work properly.'),
                                $addon[ 'application' ][ 'name' ],
                                $dependency[ '@value' ]
                            )
                        );
                        echo 'cek dependency';
                        self::disable( $addon[ 'application'][ 'namespace' ] );
                    }
                }
            }             
        }
    }

	// --------------------------------------------------------------------

    /**
     * Is Active
     *
     * Checks whether a addon is active
     */
    public static function is_active($addon_namespace, $fresh = false)
    {
        global $Options;

        if ($fresh === true) {
            $activated_addons = @$Options[ 'actives_addons' ];
        } 
        else {
            $activated_addons = self::$actives;
        }

        if ( in_array( $addon_namespace, ( array ) $activated_addons, true ) ) {
            return true;
        }
        return false;
    }

	// --------------------------------------------------------------------

    /**
     * Enable
     *
     * Enable a addon using namespace provided
     */
    public static function enable($addon_namespace)
    {
        global $Options; // get Global Options

        $activated_addons = ( array ) @$Options[ 'actives_addons' ];
        if (! in_array($addon_namespace, $activated_addons)) 
        {
            $activated_addons[] = $addon_namespace;
            get_instance()->options_model->set('actives_addons', $activated_addons);
            $Options[ 'actives_addons' ] = $activated_addons;
        }
    }

    // --------------------------------------------------------------------

    /**
    * Disable
    *
    * Disable addon using namespace provided as unique parameter
    */
    public static function disable($addon_namespace)
    {
        global $Options;

        $activated_addons = ( array ) @$Options[ 'actives_addons' ];
        if ( in_array( $addon_namespace, $activated_addons ) ) 
        {
            $key = array_search( $addon_namespace, $activated_addons );
            unset( $activated_addons[ $key ] );
            $Options[ 'actives_addons' ] = $activated_addons;
            get_instance()->options_model->set( 'actives_addons', $activated_addons);
        }
    }

    // --------------------------------------------------------------------

    public static function migration_files( $namespace, $from, $to )
    {
        $saved_files = [];
        
        if( $from == null || $to == null ) {
            return $saved_files;
        }

        $migrate_file = ADDONSPATH . $namespace . '/migrate.php';
        if( ! is_file( $migrate_file ) ) {
            return $saved_files;
        }

        $migration_files = include( $migrate_file );
        foreach( force_array($migration_files) as $version => $_file ) 
        {
            if( version_compare( $version, $from, '>' ) && version_compare( $version, $to, '<=' ) ) {
                $saved_files[ $version ][] = $_file;
            }
        }

        return $saved_files;
    }

    // --------------------------------------------------------------------

    public static function uninstall($addon_namespace)
    {
        // Disable first
        self::disable($addon_namespace);

        $addonpath = ADDONSPATH . $addon_namespace;
        $manifest_file = $addonpath . '/' . self::$manifest_addon;

        if (is_file($manifest_file)) 
        {
            $manifest_array = json_decode(file_get_contents($manifest_file), true);
            // removing file
            foreach ($manifest_array as $file) {
                if (is_file($file)):
                    unlink($file);
                endif;
            }
        }

        // Drop addon Folder
        Filer::drop($addonpath);

        // Drop Assets Folder
        if (is_dir($addon_assets_folder = FCPATH . 'assets/addons' . '/' . $addon_namespace)) {
            Filer::drop($addon_assets_folder);
        }

        // Drop Assets Folder
        if (is_dir($addon_themes_folder = FCPATH . 'assets/themes' . '/' . $addon_namespace)) {
            Filer::drop($addon_themes_folder);
        }

        // Drop Assets Folder
        if (is_dir($themes_folder = APPPATH . 'views/frontend' . '/' . $addon_namespace)) {
            Filer::drop($themes_folder);
        }

        get_instance()->options_model->delete( null, $addon_namespace);
    }

    // --------------------------------------------------------------------

    public static function extract($addon_namespace)
    {
        $addon = self::get($addon_namespace);
        if ($addon) 
        {
            get_instance()->load->library('zip');

            $addon_temp_folder_name = do_hash($addon_namespace);
            $addon_installed_dir = ADDONSPATH  . $addon_namespace . '/';
            
            // creating temp folder
            $temp_folder = APPPATH . 'temp' . '/' . $addon_temp_folder_name;
            if (!is_dir($temp_folder)) {
                mkdir($temp_folder);
            }

            // check manifest
            $manifest = $addon_installed_dir . self::$manifest_addon;
            if (is_file($manifest)) 
            {
                $manifest_array = json_decode(file_get_contents($manifest));
                // manifest is valid
                if (is_array($manifest_array)) 
                {
                    // moving manifest file to temp folder
                    foreach (self::$allowed_app_folders as $reserved_folder) 
                    {
                        foreach ($manifest_array as $file) 
                        {
                            //var_dump( $path_id_separator = APPPATH . $reserved_folder );
                            if (strstr($file, $path_id_separator = APPPATH . $reserved_folder)) 
                            {
                                // we found a a file
                                $path_splited = explode($path_id_separator, $file);
                                //var_dump( $path_splited );
                                Filer::file_copy(
                                    APPPATH . $reserved_folder . $path_splited[1],
                                    $temp_folder . '/' . $reserved_folder . $path_splited[1]
                                );
                            }
                        }
                    }
                }
            }

            // Copy Assets to
            if (is_dir($addon_assets_folder = FCPATH . 'assets/addons' . '/' . $addon_namespace)) 
            {
                // create assets folder
                $_temp_folder_assets = $temp_folder . '/' . 'assets';
                if (!is_dir($_temp_folder_assets)) {
                    mkdir($_temp_folder_assets);
                }
                Filer::copy($addon_assets_folder, $_temp_folder_assets);
            }

            // Copy Themes to
            if (is_dir($themes_folder = APPPATH . 'views/frontend' . '/' . $addon_namespace)) 
            {
                // create themes folder
                $_temp_folder_themes = $temp_folder . '/' . 'themes';
                if (!is_dir($_temp_folder_themes)) {
                    mkdir($_temp_folder_themes);
                }
                Filer::copy($themes_folder, $_temp_folder_themes);
            }

            // Copy Themes to
            if (is_dir($addon_themes_folder = FCPATH . 'assets/themes' . '/' . $addon_namespace)) 
            {
                // create themes folder
                $_temp_folder_themes_assets = $temp_folder . '/' . 'themes/assets';
                if (!is_dir($_temp_folder_themes_assets)) {
                    mkdir($_temp_folder_themes_assets);
                }
                Filer::copy($addon_themes_folder, $_temp_folder_themes_assets);
            }

            // move addon file to temp folder
            Filer::copy($addon_installed_dir, $temp_folder);

            // delete manifest file
            unlink($temp_folder . '/' . self::$manifest_addon);

            // read temp folder and download it
            get_instance()->zip->read_dir( $temp_folder . '/', false );
            
            // delete temp folder
            Filer:: drop($temp_folder);

            get_instance()->zip->download($addon[ 'application' ][ 'name' ] . '-' . $addon[ 'application' ][ 'version' ]);
        }
    }

	// --------------------------------------------------------------------

    /**
     * Sync Path
     *
     * Sync Path to register manifest files [for intenal use]
     */

    public static function sync($addon_namespace)
    {
        $folder_to_lower = strtolower($addon_namespace);
        $addon_dir_path = ADDONSPATH . $folder_to_lower;
        $relative_json_manifest = array();

        // moving manifest file to temp folder
        foreach (self::$allowed_app_folders as $reserved_folder) 
        {
            $addon_global_manifest = self::__parse_path(APPPATH . $reserved_folder);
            // if (is_array($addon_global_manifest)) 
            // {
            // }   
            // // removing raw_name from old manifest to ease copy
            // $relative_path_to_file = explode($extraction_data[ 'upload_data' ][ 'raw_name' ] . '/', $_manifest);

            // if (! is_file($_manifest)) {
            //     $dir_name = basename($_manifest);
            //     Filer::copy($_manifest, $relative_path_to_file[1]);
            // } 
            // else {
            //     // write file on the new folder
            //     Filer::file_copy($_manifest,  APPPATH . $relative_path_to_file[1]);
            //     // relative json manifest
            // }
        }
        // file_put_contents($addon_dir_path . '/' . self::$manifest_addon, json_encode($relative_json_manifest, JSON_PRETTY_PRINT));
    }

    // --------------------------------------------------------------------

    public static function install( $file_name, $asFile = true )
    {
        $config[ 'allowed_types' ] = 'zip';
        $config[ 'upload_path' ]   = APPPATH . '/' . 'temp' . '/';
        $config[ 'max_size' ]      = 50000;

        get_instance()->load->library('upload', $config);

        if (! get_instance()->upload->do_upload($file_name)) {
            return 'fetch-from-upload';
        } 
        else {
            $data = array( 'upload_data' => get_instance()->upload->data() );
            return self::__treatZipFile( $data );
        }
    }

    // --------------------------------------------------------------------

    /**
     * Treat Zip File
     * @return void
     */
    public static function __treatZipFile( $data )
    {
        // Look for manifest.json file to read manifest
        $extraction_temp_path = self::__unzip( $data );
        if (file_exists($extraction_temp_path . '/' . self::$config_addon)) 
        {
            // If manifest xml file has at least a namespace parameter
            $addon_array = json_decode(file_get_contents($extraction_temp_path . '/' . self::$config_addon), true);
            if (isset($addon_array[ 'application' ][ 'namespace' ])) 
            {
                $addon_namespace = $addon_array[ 'application' ][ 'namespace' ];
                $old_addon = self::get($addon_namespace);
                // if addon with a same namespace already exists
                if ($old_addon) {
                    if (isset($old_addon[ 'application' ][ 'version' ])) 
                    {
                        $old_version = $old_addon[ 'application' ][ 'version' ];
                        $new_version = $addon_array[ 'application' ][ 'version' ];

                        if (version_compare($new_version, $old_version, '>')) 
                        {
                            $addon_global_manifest = self::__parse_path($extraction_temp_path);

                            if (is_array($addon_global_manifest)) 
                            {
                                // Remove the old addon. No SQL uninstall queries will be triggered.
                                self::uninstall( $addon_array[ 'application' ][ 'namespace' ] );

                                // Install the new version
                                $response = self::__move_to_addon_dir($addon_array, $addon_global_manifest[0], $addon_global_manifest[1], $data);
                                
                                // Delete temp file
                                Filer::drop($extraction_temp_path);

                                if ($response !== true) {
                                    return $response;
                                } 
                                else {
                                    // if is file
                                    $migrate_file = ADDONSPATH . $addon_array[ 'application' ][ 'namespace' ] . '/migrate.php';
                                    if (is_file($migrate_file)) {
                                        return array(
                                            'namespace' => $old_addon[ 'application' ][ 'namespace' ],
                                            'from' => $old_addon[ 'application' ][ 'version' ],
                                            'msg' => 'addon-updated-migrate-required'
                                        );
                                    }

                                    // Enable back the addon
                                    self::enable($addon_array[ 'application' ][ 'namespace' ]);

                                    // Return details
                                    return array(
                                        'namespace' => $addon_array[ 'application' ][ 'namespace' ],
                                        'from' => $addon_array[ 'application' ][ 'version' ],
                                        'msg' => 'addon-updated'
                                    );
                                }
                            }
                            // If it's not an array, return the error code.
                            return $addon_global_manifest;
                        }
                        // Delete temp file
                        Filer::drop($extraction_temp_path);
                        return 'old-version-cannot-be-installed';
                    }

                    /**
                     * Update is done only when addon has valid version number
                     * Update is done only when new addon version is higher than the old version
                    **/

                    // Delete temp file
                    Filer::drop($extraction_temp_path);
                    return 'unable-to-update';
                }
                // if addon does'nt exists
                else 
                {
                    $addon_global_manifest = self::__parse_path($extraction_temp_path);

                    if (is_array($addon_global_manifest)) 
                    {
                        $response = self::__move_to_addon_dir($addon_array, $addon_global_manifest[0], $addon_global_manifest[1], $data, true);

                        // Delete temp file
                        Filer::drop($extraction_temp_path);

                        if ($response !== true) {
                            return $response;
                        } 
                        else {
                            return array(
                                'namespace' => $addon_array[ 'application' ][ 'namespace' ],
                                'from' => $addon_array[ 'application' ][ 'version' ],
                                'msg' => 'addon-installed'
                            );
                        }
                    }
                    // If it's not an array, return the error code.
                    return $addon_global_manifest;
                }
            }
            // Delete temp file
            Filer::drop($extraction_temp_path);
            return 'manifest-file-incorrect';
        }
        // Delete temp file
        Filer::drop($extraction_temp_path);
        return 'manifest-file-not-found';
    }

    // --------------------------------------------------------------------
    
    /**
     * Unzip
     *
     * Unzip an Uploaded addon
     */
    public static function __unzip($upload_details)
    {
        $extraction_path = $upload_details[ 'upload_data' ][ 'file_path' ] . $upload_details[ 'upload_data' ][ 'raw_name' ] ;
        
        // If temp path does'nt exists
        if (! is_dir($extraction_path)): mkdir($extraction_path);
        endif;

        get_instance()->load->library('unzip');

        get_instance()->unzip->extract(
            $upload_details[ 'upload_data' ][ 'full_path' ],
            $extraction_path
        );

        get_instance()->unzip->close();

        // delete zip file
        if (is_file($upload_details[ 'upload_data' ][ 'full_path' ])): unlink($upload_details[ 'upload_data' ][ 'full_path' ]);
        endif;

        // Fix addon installed from Github
        if (! file_exists($extraction_path . '/' . self::$config_addon)) 
        {
            $temp_dir = opendir($extraction_path);

            while (false !== ($file = readdir($temp_dir))) 
            {
                if (! in_array($file, array( '.', '..' ))) 
                {
                    // if within this folder a config file exists
                    if (file_exists($extraction_path . '/' . $file . '/' . self::$config_addon)) 
                    {
                        Filer::copy($extraction_path . '/' . $file, $extraction_path); // moving folder to the top parent folder
                        Filer::drop($extraction_path . '/' . $file);
                    }
                }
            }
        }

        return $extraction_path;
    }

	// --------------------------------------------------------------------

    /**
     * Parse Path
     *
     * Parse Path to register manifest files [for intenal use]
     */

    public static function __parse_path($path)
    {
        $manifest = array();
        $addon_manifest = array();
        if ($dir = opendir($path)) {
            while (false !== ($file = readdir($dir))) 
            {
                if (! in_array($file, array( '.', '..' ), true)) 
                {
                    // Set sub dir path
                    $sub_dir_path = $path;
                    // If a correct folder is found
                    if (in_array($file, self::$allowed_app_folders) && is_dir($path . '/' . $file)) 
                    {
                        // var_dump( $sub_dir_path . '/' . $file . '/' );
                        $manifest = array_merge($manifest, self::__scan($sub_dir_path . '/' . $file));
                    }
                    else {
                        // for other file and folder, they are included in addon dir
                        $addon_manifest[] = $sub_dir_path . '/' . $file;
                    }
                }
            }
            closedir($dir);

            // When everything seems to be alright
            return array( $addon_manifest , $manifest );
        }
        return 'extraction-path-not-found';
    }

	// --------------------------------------------------------------------

    /**
     * Scan
     *
     * Scan folder and returns content as array
     */
    public static function __scan($folder)
    {
        $files_array = array();
        if (is_dir($folder)) 
        {
            $folder_res = opendir($folder);
            while (false !== ($file = readdir($folder_res))) 
            {
                if (! in_array($file, array( '.', '..' ))) 
                {
                    if (is_dir($folder . '/' . $file)) {
                        $files_array = array_merge($files_array, self::__scan($folder . '/' . $file));
                    } 
                    else {
                        $files_array[] = $folder . '/' . $file;
                    }
                }
            }
            closedir($folder_res);
            return $files_array;
        }
        return false;
    }

	// --------------------------------------------------------------------

    /**
     * Mode To addon Dir
     *
     * Move addon temp fil to valid addon folder [for internal use only]
     */

    public static function __move_to_addon_dir($addon_array, $global_manifest, $manifest, $extraction_data, $conflict_checker = false)
    {
        $addon_namespace = $addon_array[ 'application' ][ 'namespace' ]; // addon namespace
        if ($conflict_checker === true) 
        {
            // Check first
            foreach ($manifest as $_manifest_file) {
                // removing raw_name from old manifest to ease copy
                $relative_path_to_file = explode($extraction_data[ 'upload_data' ][ 'raw_name' ] . '/', $_manifest_file);
                $_manifest_file = APPPATH . $relative_path_to_file[1];

                if (file_exists($_manifest_file)) : return array(
                    'msg' => 'file-conflict',
                    'namespace'=>$addon_namespace,
                    'extra' => urlencode($_manifest_file)
                );
                endif;
            }
        }

        get_instance()->load->helper('file');
        $folder_to_lower = strtolower($addon_namespace);
        $addon_dir_path = ADDONSPATH . $folder_to_lower;

        // Creating addon folder within
        if (! is_dir(ADDONSPATH . $folder_to_lower)) {
            mkdir(ADDONSPATH . $folder_to_lower, 0777, true);
        }

        // moving global manifest
        foreach ($global_manifest as $_manifest) 
        {
            // creating folder if it does'nt exists
            if (! is_file($_manifest)) {
                $dir_name = basename($_manifest);
                Filer::copy($_manifest, $addon_dir_path . '/' . $dir_name);
            } 
            else {
                $file_name = basename($_manifest);
                write_file($addon_dir_path . '/' . $file_name, file_get_contents($_manifest));
            }
        }

        $relative_json_manifest = array();

        // moving manifest to system folder
        foreach ($manifest as $_manifest) 
        {
            // removing raw_name from old manifest to ease copy
            $relative_path_to_file = explode($extraction_data[ 'upload_data' ][ 'raw_name' ] . '/', $_manifest);

            if (! is_file($_manifest)) {
                $dir_name = basename($_manifest);
                Filer::copy($_manifest, $relative_path_to_file[1]);
            } 
            else {
                // write file on the new folder
                Filer::file_copy($_manifest,  APPPATH . $relative_path_to_file[1]);
                // relative json manifest
                $relative_json_manifest[] = APPPATH . $relative_path_to_file[1];
            }
        }
        // Creating Manifest
        file_put_contents($addon_dir_path . '/' . self::$manifest_addon, json_encode($relative_json_manifest, JSON_PRETTY_PRINT));

        /**
         * New Feature Assets management
         * Description : move addon assets to public directory within a folder with namespace as name
        **/
        if (is_dir($addon_dir_path . '/' . 'assets')) 
        {
            $addon_assets_folder = FCPATH . 'assets/addons' . '/' . $addon_namespace;
            if (is_dir($addon_assets_folder)) { // checks if addon folder exists on public folder
                Filer::drop($addon_assets_folder);
            }

            mkdir($addon_assets_folder); // creating addon folder within
            Filer::extractor($addon_dir_path . '/' . 'assets', $addon_assets_folder);
        }

        /**
         * New Feature Assets management
         * Description : move addon assets to public directory within a folder with namespace as name
        **/
        if (is_dir($addon_dir_path . '/' . 'themes/assets')) 
        {
            $addon_themes_folder = FCPATH . 'assets/themes' . '/' . $addon_namespace;
            if (is_dir($addon_themes_folder)) { // checks if addon folder exists on public folder
                Filer::drop($addon_themes_folder);
            }

            mkdir($addon_themes_folder); // creating addon folder within
            Filer::extractor($addon_dir_path . '/' . 'themes/assets', $addon_themes_folder);
        }

        /**
         * New Feature Assets management
         * Description : move addon assets to public directory within a folder with namespace as name
        **/
        if (is_dir($addon_dir_path . '/' . 'themes')) 
        {
            $themes_folder = APPPATH . 'views/frontend' . '/' . $addon_namespace;
            if (is_dir($themes_folder)) { // checks if addon folder exists on public folder
                Filer::drop($themes_folder);
            }

            mkdir($themes_folder); // creating addon folder within
            Filer::extractor($addon_dir_path . '/' . 'themes', $themes_folder);
        }

        return true;
    }
}