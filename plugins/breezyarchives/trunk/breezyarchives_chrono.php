<?php
	if (count($years) > 0) {
		echo '<ul id="breezy-chronology-archive">';
		foreach ($years as $year => $months) {
			printf('<li class="year"><a href="%1$s" rel="archives">%2$s</a><ul class="breezy-yearly-archive">',
					URL::get('display_entries_by_date', array('year' => $year)),
					$year);
			if ($show_monthly_post_count) {
				foreach ($months as $month => $count) {
					if ($count > 0) {
						printf('<li class="month"><a href="%1$s" rel="archives">%2$s</a> <span class="post-count" title="%3$s">%4$d</span></li>',
								URL::get('display_entries_by_date', array('year' => $year, 'month' => $month)),
								Utils::locale_date($month_format, mktime(0,0,0,$month,1)),
								sprintf(_n('%1$d Post', '%1$d Posts', $count, $this->class_name), $count),
								$count);
					} else {
						printf('<li class="month"><span>%1$s</span> <span class="post-count" title="%2$s">%3$d</span></li>',
								Utils::locale_date($month_format, mktime(0,0,0,$month,1)),
								sprintf(_n('%1$d Post', '%1$d Posts', $count, $this->class_name), $count),
								$count);
					}
				}
			} else {
				foreach ($months as $month => $count) {
					if ($count > 0) {
						printf('<li class="month"><a href="%1$s" rel="archives">%2$s</a></li>',
								URL::get('display_entries_by_date', array('year' => $year, 'month' => $month)),
								Utils::locale_date($month_format, mktime(0,0,0,$month,1)));
					} else {
						printf('<li class="month"><span>%1$s</span></li>',
								Utils::locale_date($month_format, mktime(0,0,0,$month,1)));
					}
				}
			}
			echo '</ul></li>';
		}
		echo '</ul>';
	}
?>