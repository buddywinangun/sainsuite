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
        'name'   => __('Add A group'),
        'icon'    => 'ki ki-plus',
        'attr_anchor'  => 'class="btn btn-light-primary btn-sm font-weight-bolder"',
        'slug'    => [ 'admin', 'group', 'add' ],
        'permission' => 'create.group'
    );
    return $final;
});

$this->events->add_filter('toolbar_filter', function ($filter) { // disabling header
    $filter[] = '
    <div class="row">
        <div class="input-icon col mb-1 mb-sm-0">
            <input type="text" class="form-control form-control-sm" placeholder="Search..." id="search_query" />
            <span><i class="flaticon2-search-1 text-muted"></i></span>
        </div>
    </div>';

    return $filter;
});

/**
 * Meta
 */
$this->polatan->add_meta(array(
    'namespace'  => 'group',
    'col_id' => 1,
    'class'     => 'col-12',
    'type' => 'card'
));

/**
 * Item
 */
$this->polatan->add_item(array(
    'type'  => 'table-datatable',
), 'group');

/**
 * Script
 */
$this->events->add_action( 'dashboard_footer', function() {
    $this->load->addon_view( 'users', 'group/datatable');
});

$this->polatan->output();