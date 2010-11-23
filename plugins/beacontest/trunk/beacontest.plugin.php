<?php

	class BeaconTest extends Plugin {
		
		public function action_update_check ( ) {
			
			// add an extra GUID, in addition to the one in our .xml file to pretend a theme was registered
			Update::add( 'Test', '10cbaf18-bdd2-48e1-b0d3-e1853a4c649d', '3.0.2' );
			
			// add an extra GUID, in addition to the one in our .xml file to pretend a plugin was registered
			Update::add( 'Test', '7a0313be-d8e3-11d1-8314-0800200c9a66', '0.9' );
			
		}
		
	}
	
?>