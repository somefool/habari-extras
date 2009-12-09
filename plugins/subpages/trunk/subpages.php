<span>Subpages:</span>
<?php
$out = array();
foreach ( $subpages as $subpage ) {
	$out[] = "<a href='{$subpage->permalink}'>{$subpage->title}</a>";
}
echo join(' | ', $out);
?>
