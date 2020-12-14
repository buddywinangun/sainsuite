<?php

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

$this->polatan->add_col(array(
    'width' => 1,
    'class' => 'flex-row-auto offcanvas-mobile aside-profile',
    'id'=> 'kt_profile_aside',
), 1);

$this->polatan->add_meta(array(
    'col_id'    => 1,
    'namespace' => 'profile',
    'type' => 'card'
));

$this->polatan->add_item( array(
    'type'    => 'dom',
    'content' => $this->addon_view( 'users', 'profile/avatar', array(), true )
), 'profile',  1 );

$this->polatan->add_item( array(
    'type'    => 'dom',
    'content' => $this->addon_view( 'users', 'profile/menu', array(), true )
), 'profile',  1 );

/**
 * 
 */

$this->polatan->add_col(array(
    'width' => 3,
    'class' => 'flex-row-fluid',
), 2);

$this->polatan->add_meta(array(
    'col_id'    => 2,
    'namespace' => 'user_profile',
    'gui_saver' => true,
    'form'      => array(
        'action' => null
    ),
    'type' => 'card',
    'header'    =>    array(
        'title'    =>  'Personal Information',
        'sub_title' => 'Update your personal informaiton'
    ),
    'footer'    =>    array(
        'submit'    =>    array(
            'label' => __('Edit User')
        )
    ),
));

// User name
$this->polatan->add_item(array(
    'type'     => 'text',
    'cols'  => array(
        [
            'label'    => __('User Name', 'aauth'),
            'name'     => 'username',
            'disabled' => true,
            'value'    => set_value('username', User::get()->username) 
        ],
        [
            'label' => __('User Email', 'aauth'),
            'name'  => 'user_email',
            'disabled' => true,
            'value'    => set_value('user_email', User::get()->email) 
        ]
    )
), 'user_profile', 2);

// user password
$this->polatan->add_item(array(
    'type'  => 'password',
    'label' => __('Old Password', 'aauth'),
    'name'  => 'old_pass',
), 'user_profile', 2);

$this->polatan->add_item(array(
    'type'  => 'password',
    'cols'  => array(
        [
            'label' => __('New Password', 'aauth'),
            'name'  => 'password',
        ],
        [
            'label' => __('Confirm New', 'aauth'),
            'name'  => 'confirm',
        ]
    )
), 'user_profile', 2);

// load custom field for user creatin
$this->events->do_action('load_users_custom_fields', array(
    'meta_namespace' => 'user_profile',
    'col_id'         => 2,
    'gui'            => $this->polatan,
    'user_id'        => User::get()->id
));

// Connected Apps
// $this->polatan->add_meta(array(
//     'col_id'    => 2,
//     'namespace' => 'user_apps',
//     'type' => 'card',
//     'header'    =>    array(
//         'title'     => __( 'Connected Applications'),
//         'sub_title' => 'Change your settings'
//     ),
//     'gui_saver' => false,
//     'form'      => array(
//         'action' => null
//     )
// ));

$this->polatan->output();