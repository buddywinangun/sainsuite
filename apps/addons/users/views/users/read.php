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
        'name'   => __('Add A user'),
        'icon'    => 'ki ki-plus',
        'attr_anchor'  => 'class="btn btn-light-primary btn-sm font-weight-bolder"',
        'slug'    => [ 'admin', 'users', 'add' ],
        'permission' => 'create.users'
    );
    return $final;
});

$this->events->add_filter('toolbar_filter', function ($filter) { // disabling header
    $groups = $this->aauth->list_groups();
    $option = '<option value="">All</option>';
    foreach ( force_array($groups) as $gr ) {
        $option .= '<option value="'.strtolower($gr->name).'">'.$gr->definition.'</option>';
    }
    $filter[] = '
    <div class="d-flex align-items-center mr-2">
        <select class="form-control form-control-sm"
            id="kt_datatable_search_group">
            '.$option.'
        </select>
    </div>
    <div class="d-flex align-items-center mr-2">
        <select class="form-control form-control-sm"
            id="kt_datatable_search_status">
            <option value="">All</option>
            <option value="0">Active</option>
            <option value="1">Unactive</option>
        </select>
    </div>
    <div class="input-icon mr-2">
        <input type="text" class="form-control form-control-sm" placeholder="Search..." id="search_query" />
        <span><i class="flaticon2-search-1 text-muted"></i></span>
    </div>';

    return $filter;
});

$this->polatan->add_meta(array(
    'namespace' => 'users',
    'class' => 'col-12',
    'card' => 'card-px-0 border-0',
    'col_id' => 1,
    'type' => 'card'
));

$this->polatan->add_item(array(
    'type' => 'table-datatable',
    'data' => json_decode($users),
), 'users');

$this->events->add_action( 'dashboard_footer', function() {
    $this->load->addon_view( 'users', 'users/datatable');
});

$this->polatan->output();