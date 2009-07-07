<table class="calendar" summary="calendar">
  <caption>
    <a href="<?php echo $prev_month_url; ?>">&laquo;</a>
    <?php echo $year . ' / ' . $month; ?>
    <a href="<?php echo $next_month_url; ?>">&raquo;</a>
  </caption>
<?php for ($week = 0; $week < count($calendar); $week++): ?>
  <?php if ($week == 0): ?>
  <tr>
    <?php for ($wday = 0; $wday < count($calendar[$week]); $wday++): ?>
    <th class="<?php echo strtolower($calendar[$week][$wday]['label']); ?>"><?php echo $calendar[$week][$wday]['label']; ?></th>
    <?php endfor; ?>
  </tr>
  <?php else: ?>
  <tr>
    <?php for ($wday = 0; $wday < count($calendar[$week]); $wday++): ?>
      <?php if (isset($calendar[$week][$wday]['url'])): ?>
    <td><a href="<?php echo $calendar[$week][$wday]['url']; ?>"><?php echo $calendar[$week][$wday]['label']; ?></a></td>
      <?php else: ?>
    <td><?php echo $calendar[$week][$wday]['label']; ?></td>
      <?php endif; ?>
    <?php endfor; ?>
  </tr>
  <?php endif; ?>
<?php endfor; ?>
</table>
