<?php

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

// Toolbar
$this->events->add_filter( 'toolbar_nav', function( $final ) {
    $final[] = array(
        'id' => 1,
        'name'   => __('Back to the list'),
        'icon'    => 'ki ki-long-arrow-back',
        'attr_anchor'  => 'class="btn btn-light btn-sm font-weight-bolder"',
        'slug'    => [ 'admin', 'users' ],
        'permission' => 'create.users'
    );
    return $final;
});

$this->polatan->add_meta(array(
    'namespace' => 'users',
    'class' => 'col-12',
    'col_id' => 1,
    'gui_saver' => false,
    'type' => 'card',
    'form' => array(
        'action' => null,
    ),
    'footer' => 'add',
));

$item[] = array(
    'id' => 1,
    'heading'=> __('Personal Information'),
    'class' => 'card-header-light',
    'description' => 'Update your personal informaiton',
    'show' => true,
    'hide_footer' => true,
    'body' => array(
        'items' => $this->events->apply_filters_ref_array('load_user_profile', array( 
            array_merge(
                ['user'=> null], 
                ['groups'=> $groups], 
                ['user_group'=> null], 
                ['page'=> 'users']
            )
         ))
    )
);
$item[] = array(
    'id' => 2,
    'heading'=> __('Change Password'),
    'class' => 'card-header-light',
    'description' => 'Change your account password',
    'show' => true,
    'hide_footer' => true,
    'body' => array(
        'items' => $this->events->apply_filters_ref_array('load_user_pass', array( 
            array_merge(
                ['user'=> null], 
                ['page'=> 'users']
            )
         ))
    )
);

$this->polatan->add_item(array(
    'type'  => 'accordions',
    'accordion' => $item
), 'users');

$this->polatan->output();