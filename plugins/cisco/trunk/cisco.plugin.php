<?php
/*
 * Pays homage to the 2008-09-25 cisco hack.
 * More here: http://h0bbel.p0ggel.org/cisco-and-missing-t-s
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class Cisco extends Plugin
{
	public function info()
	{
		return array(
			'name' => 'Cisco',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => "0.1",
			'description' => 'Pays homage to the 2008-09-25 cisco hack.',
			'license' => 'Apache License 2.0'
		);
	}
	
	public function filter_final_output( $buffer )
	{
		return str_replace( 't', '', $buffer );
	}
	
}

?>
