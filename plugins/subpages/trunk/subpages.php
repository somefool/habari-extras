<span>Subpages:</span>
<?php
$out = array();
foreach ( $subpages as $page ) {
//$out[] = "<a href='{$page->permalink}'>{$page->title}</a>";
$out[] = "<a href='{$page->term}'>{$page->term_display}</a>";
}
echo join(' | ', $out);
?>
