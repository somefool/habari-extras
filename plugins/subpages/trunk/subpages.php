<span>Subpages:</span>
<?php
$out = array();
foreach ( $subpages as $page ) {
	$out[] = "<a href='{$page->permalink}'>{$page->title}</a>";
}
echo join(' | ', $out);
?>
