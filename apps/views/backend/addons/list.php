<?php

$this->polatan->col_width(1, 4);

$this->polatan->add_meta(array(
    'col_id' => 1,
    'namespace' => 'addons'
));

$this->polatan->add_item(array(
    'type'    => 'dom',
    'content' => $this->load->view('backend/addons/list_dom', array(), true )
), 'addons', 1);

$this->polatan->output();