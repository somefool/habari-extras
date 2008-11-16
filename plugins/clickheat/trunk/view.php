<?php
/**
 * Clickehat by Benjamin Hutchins
 * @see Plugin info for more information
 */

$months = explode(',', '0,January,February,March,April,May,June,July,August,September,October,November,December');
$calendar= $this->create_calendar();

$theme->display( 'header' );

$prevCal= ($this->month == 1 ? $this->year - 1 : $this->year) . '-' . ($this->month == 1 ? 12 : sprintf('%02d', $this->month - 1)) . '-01';
$nextCal= ($this->month == 12 ? $this->year + 1 : $this->year) . '-' . ($this->month == 12 ? '01' : sprintf('%02d', $this->month + 1)) . '-01';
?>

<div class="create">
<div class="container">
	<span class="viewpost">
		<a href="#" onclick="clickheat.toggleVisibility(this); return false;" alt="<?php _e('Show options'); ?>"><?php _e('Hide options'); ?></a> | 
		<a href="#" onclick="clickheat.updateHeatmap(); return false;"><?php _e('Refresh'); ?></a>
	</span>
	<span id="cleaner">&nbsp;</span></span>
</div>
</div>

<div class="container settings" id="options">
	<h2>Clickheat <?php _e('View'); ?></h2>
	<div class="pct50">
		<?php echo $theme->form; ?>
	</div>
	<div class="pct35" style="float: right;">
		<table>
		<tr>
			<td rowspan="4"><?php echo $calendar; ?></td>
			<td>
				<table class="clickheat-calendar">
				<tr><th>
					<a href="<?php URL::out( 'admin', array('page'=>'clickheat', 'date'=>$prevCal) ); ?>">&lt;</a>&nbsp;<?php _e( $months[$this->month] ); ?>&nbsp;<a href="<?php URL::out( 'admin', array('page'=>'clickheat', 'date'=>$nextCal) ); ?>">&gt;</a>
				</th></tr>
				<tr><td id="clickheat-calendar-d"><a href="#" onclick="clickheat.range='d'; this.blur(); clickheat.updateCalendar(); return false;"><?php _e('Day'); ?></a></td></tr>
				<tr><td id="clickheat-calendar-w"><a href="#" onclick="clickheat.range='w'; this.blur(); clickheat.updateCalendar(); return false;"><?php _e('Week'); ?></a></td></tr>
				<tr><td id="clickheat-calendar-m"><a href="#" onclick="clickheat.range='m'; this.blur(); clickheat.updateCalendar(); return false;"><?php _e('Month'); ?></a></td></tr>
				</table>
			</td>
		</tr>
		</table>
	</div>
</div>

<div class="">
	<div id="overflowDiv">
		<div id="pngDiv"></div>
		<iframe src="about:blank" id="webPageFrame" frameborder="0" scrolling="no" width="50" height="0"></iframe>
	</div>
</div>

<?php $theme->display('footer'); ?>
