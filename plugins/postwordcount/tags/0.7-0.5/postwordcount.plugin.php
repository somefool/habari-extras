<?php 

/**********************************
 *
 * Word Count for Posts
 * 
 * usage: <?php echo $post->word_count; ?>
 *
 *********************************/

class PostWordCount extends Plugin
{
	public function action_post_update_after( $post )
	{
		$allcharacters = 'ÁÀÂÄǍĂĀÃÅǺĄƁĆĊĈČÇĎḌƊÉÈĖÊËĚĔĒĘẸƎƏƐĠĜǦĞĢƔĤḤĦIÍÌİÎÏǏĬĪĨĮỊĴĶƘĹĻŁĽĿŃŇÑŅÓÒÔÖǑŎŌÕŐỌØǾƠŔŘŖŚŜŠŞȘṢŤŢṬÚÙÛÜǓŬŪŨŰŮŲỤƯẂẀŴẄǷÝỲŶŸȲỸƳŹŻŽẒáàâäǎăāãåǻąɓćċĉčçďḍɗéèėêëěĕēęẹǝəɛġĝǧğģɣĥḥħıíìiîïǐĭīĩįịĵķƙĸĺļłľŀŉńňñņóòôöǒŏōõőọøǿơŕřŗśŝšşșṣſťţṭúùûüǔŭūũűůųụưẃẁŵẅƿýỳŷÿȳỹƴźżžẓΑΆΒΓΔΕΈΖΗΉΘΙΊΪΚΛΜΝΞΟΌΠΡΣΤΥΎΫΦΧΨΩΏαάβγδεέζηήθιίϊΐκλμνξοόπρσςτυύϋΰφχψωώÆǼǢÐĐĲŊŒÞŦæǽǣðđĳŋœßþŧ';
		$post->info->wordcount = str_word_count( strip_tags( ( $this->config[ 'add_title' ] ? $post->content . " {$post->title}" : $post->content ) ), 0, $allcharacters );
		$post->info->commit();
	}
	
	public function configure()
    {
		$class_name= strtolower( get_class( $this ) );
		$ui= new FormUI( $class_name );
		$add_title= $ui->append( 'checkbox', 'add_title',
        $class_name . '__add_title', _t( 'Include title words in count?' ) );
		$ui->append( 'submit', 'save', 'save' );
		return $ui;
    }

	public function action_init()
	{
		$class_name= strtolower( get_class( $this ) );
		$this->config[ 'add_title' ]= Options::get( $class_name . '__add_title' );
	}
	
	public function filter_post_word_count( $word_count, $post ) 
	{
		$allcharacters = 'ÁÀÂÄǍĂĀÃÅǺĄƁĆĊĈČÇĎḌƊÉÈĖÊËĚĔĒĘẸƎƏƐĠĜǦĞĢƔĤḤĦIÍÌİÎÏǏĬĪĨĮỊĴĶƘĹĻŁĽĿŃŇÑŅÓÒÔÖǑŎŌÕŐỌØǾƠŔŘŖŚŜŠŞȘṢŤŢṬÚÙÛÜǓŬŪŨŰŮŲỤƯẂẀŴẄǷÝỲŶŸȲỸƳŹŻŽẒáàâäǎăāãåǻąɓćċĉčçďḍɗéèėêëěĕēęẹǝəɛġĝǧğģɣĥḥħıíìiîïǐĭīĩįịĵķƙĸĺļłľŀŉńňñņóòôöǒŏōõőọøǿơŕřŗśŝšşșṣſťţṭúùûüǔŭūũűůųụưẃẁŵẅƿýỳŷÿȳỹƴźżžẓΑΆΒΓΔΕΈΖΗΉΘΙΊΪΚΛΜΝΞΟΌΠΡΣΤΥΎΫΦΧΨΩΏαάβγδεέζηήθιίϊΐκλμνξοόπρσςτυύϋΰφχψωώÆǼǢÐĐĲŊŒÞŦæǽǣðđĳŋœßþŧ';
		return str_word_count( strip_tags( ( $this->config[ 'add_title' ] ? $post->content . " {$post->title}" : $post->content ) ), 0, $allcharacters ); 	
	}
}
?>
