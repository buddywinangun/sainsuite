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
        'id'          => 1,
        'name'        => __('Back to the list'),
        'icon'        => 'ki ki-long-arrow-back',
        'attr_anchor' => 'class="btn btn-light btn-sm font-weight-bolder"',
        'slug'        => [ 'admin', 'group' ]
    );
    return $final;
});

/**
 * Meta
 */
$this->polatan->add_meta(array(
    'col_id'    => 1,
    'class'     => 'col-12',
    'namespace' => 'group',
    'gui_saver' => false,
    'form'      => array(
        'action' => null,
    ),
    'footer' => (isset($group)) ? 'edit' : 'add',
    'type' => 'card',
));


/**
 * Item
 */
if( $this->events->apply_filters('fill_cek_sub_groups', true) ):

$this->polatan->add_item(array(
    [
        'type'  => 'text',
        'class' => 'col-6',
        'label' => __('Name'),
        'name'  => 'name',
        'value' => (isset($group))
            ? set_value('name', $group->name) 
            : set_value('name'),
    ],
    [
        'type'  => 'text',
        'class' => 'col-6',
        'label' => __('Definition'),
        'name'  => 'definition',
        'value' => (isset($group))
            ? set_value('definition', $group->definition) 
            : set_value('definition'),
    ]
), 'group');

else :

$this->events->do_action('do_form_group', '');

endif;

$this->polatan->output();