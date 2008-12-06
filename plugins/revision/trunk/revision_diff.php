<?php
/*
    Diff

	(C) Paul Butler 2007 <http://www.paulbutler.org/>
	May be used and distributed under the zlib/libpng license.

	modified from http://www.paulbutler.org/projects/simplediff/simplediff.phps
*/
class RevisionDiff
{
	public static function format_diff( $old, $new )
	{
		$old = str_replace( "\r", '', trim( $old ) );
		$new = str_replace( "\r", '', trim( $new ) );
		$old_lines = explode( "\n", $old );
		$new_lines = explode( "\n", $new );

		$diff = self::diff( $old_lines, $new_lines );
		$html = '<table class="diff" style="width: 100%;">';

		$diff_count = count( $diff );
		for ( $i = 0; $i < $diff_count; $i++ ) {
			if ( is_array( $diff[$i] ) && ( !empty( $diff[$i]['d'] ) || !empty( $diff[$i]['i'] ) ) ) {
				$del_count = count( $diff[$i]['d'] );
				$ins_count = count( $diff[$i]['i'] );

				if ( isset( $diff[$i - 1] ) &&  is_string( $diff[$i - 1] ) ) {
					$html.= '<tr><td colspan="4" style="width: 100%;">' . ( $i - 1 ) . ' Line</td></tr>';
					$html.= '<tr><td style="width: 5%;"></td><td style="width: 45%; background-color: #efefef;">' . $diff[$i - 1] . '</td><td style="width: 5%;"></td><td style="width: 45%; background-color: #efefef;">' . $diff[$i - 1] . '</td></tr>';
				}
				else {
					$html.= '<tr><td colspan="4" style="width: 100%;">' . $i . ' Line</td></tr>';
				}

				$html.= '<tr>';

				// Replace
				if ( $del_count != 0 && $ins_count != 0 ) {
					$html.= '<td style="width: 5%; text-align: right;">-</td><td style="width: 45%; background-color: #ffffbb;">';

					for ( $j = 0; $j < $del_count; $j++ ) {
						$html.= $diff[$i]['d'][$j] . '<br />';
					}
					$html.= '</td><td style="width: 5%; text-align: right;">+</td><td style="width: 45%; background-color: #eeffee;">';

					for ( $j = 0; $j < $ins_count; $j++ ) {
						$html.= $diff[$i]['i'][$j] . '<br />';
					}
					$html.= '</td>';
				}
				// Delete
				elseif ( $del_count != 0 ) {
					$html.= '<td style="width: 5%; text-align: right;">-</td><td style="width: 45%; background-color: #ffffbb;">';

					for ( $j = 0; $j < $del_count; $j++ ) {
						$html.= $diff[$i]['d'][$j] . '<br />';
					}
					$html.= '</td><td colspan="2" class="pct35"></td>';
				}
				// Insert
				elseif ( $ins_count != 0 ) {
					$html.= '<td colspan="2" style="width: 50%;"></td><td style="width: 5%; text-align: right;">+</td><td style="width: 45%; background-color: #eeffee;">';

					for ( $j = 0; $j < $ins_count; $j++ ) {
						$html.= $diff[$i]['i'][$j] . '<br />';
					}
					$html.= '</td>';
				}
				$html.= '</tr>';

				if ( isset( $diff[$i + 1] ) &&  is_string( $diff[$i + 1] ) ) {
					$html.= '<tr><td style="width: 5%;"></td><td style="width: 45%; background-color: #efefef;">' . $diff[$i + 1] . '</td><td style="width: 5%;"></td><td style="width: 45%; background-color: #efefef;">' . $diff[$i + 1] . '</td></tr>';
				}
			}
		}

		$html.= '</table>';

		return $html;
	}

	/**
	 * Diff Lines
	 *
	 * @access public
	 */
	public static function diff( $old_lines, $new_lines )
	{
		$maxlen = 0;
		foreach ($old_lines as $oindex => $ovalue ) {
			$nkeys = array_keys( $new_lines, $ovalue );
			foreach ($nkeys as $nindex) {
				$matrix[$oindex][$nindex]= isset( $matrix[$oindex - 1][$nindex - 1] ) ? $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
				if ( $matrix[$oindex][$nindex] > $maxlen ){
					$maxlen = $matrix[$oindex][$nindex];
					$omax = $oindex + 1 - $maxlen;
					$nmax = $nindex + 1 - $maxlen;
				}
			}
		}
		if ( $maxlen == 0 ) return array( array( 'd' => $old_lines, 'i' => $new_lines ) );
		return array_merge(
		self::diff( array_slice( $old_lines, 0, $omax ), array_slice( $new_lines, 0, $nmax ) ),
		array_slice( $new_lines, $nmax, $maxlen ),
		self::diff( array_slice( $old_lines, $omax + $maxlen ), array_slice( $new_lines, $nmax + $maxlen ) ) );
	}
}
?>