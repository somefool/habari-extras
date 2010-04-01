<h3>Monthly Archives</h3>
<ul>
  <?php foreach( $content->months as $month ): ?>
    <li>
	<a href="<?php echo $month[ 'url' ]; ?>" title="View entries in <?php
		echo $month[ 'display_month' ] . ", " . $month[ 'year' ];
	?>"><?php
		echo $month[ 'display_month' ] . " " . $month[ 'year' ] . $month[ 'count' ];
	?></a>
    </li>
  <?php endforeach; ?>
</ul>