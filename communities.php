<?php
require_once('admin.php');
$title = __('Communities');
$parent_file = 'communities.php';
require_once('admin-header.php');

Communities_output();

include('admin-footer.php');
?>