<?php

	class Acronyms extends Plugin {
				
		public $acronyms = array(
			'AFAIK' => 'As far as I know',
			'AIM' => 'AOL Instant Messenger',
			'AJAX' => 'Asynchronous JavaScript and XML',
			'AOL' => 'America Online',
			'API' => 'Application Programming Interface',
			'ASAP' => 'As soon as possible',
			'ASCII' => 'American Standard Code for Information Interchange',
			'ASP' => 'Active Server Pages',
			'BTW' => 'By The Way',
			'CD' => 'Compact Disc',
			'CGI' => 'Common Gateway Interface',
			'CMS' => 'Content Management System',
			'CSS' => 'Cascading Style Sheets',
			'CVS' => 'Concurrent Versions System',
			'DBA' => 'Database Administrator',
			'DHTML' => 'Dynamic HyperText Markup Language',
			'DMCA' => 'Digital Millenium Copyright Act',
			'DNS' => 'Domain Name Server',
			'DOM' => 'Document Object Model',
			'DTD' => 'Document Type Definition',
			'DVD' => 'Digital Versatile Disc',
			'EOF' => 'End of file',
			'EOL' => 'End of line',
			'EOM' => 'End of message',
			'EOT' => 'End of text',
			'FAQ' => 'Frequently Asked Questions',
			'FDL' => 'GNU Free Documentation License',
			'FTP' => 'File Transfer Protocol',
			'FUD' => 'Fear, Uncertainty, and Doubt',
			'GB' => 'Gigabyte',
			'GHz' => 'Gigahertz',
			'GIF' => 'Graphics Interchange Format',
			'GPL' => 'GNU General Public License',
			'GUI' => 'Graphical User Interface',
			'HDD' => 'Hard Disk Drive',
			'HTML' => 'HyperText Markup Language',
			'HTTP' => 'HyperText Transfer Protocol',
			'IANAL' => 'I am not a lawyer',
			'ICANN' => 'Internet Corporation for Assigned Names and Numbers',
			'IE' => 'Internet Explorer',
			'IE5' => 'Internet Explorer 5',
			'IE6' => 'Internet Explorer 6',
			'IIRC' => 'If I remember correctly',
			'IIS' => 'Internet Information Services',
			'IM' => 'Instant Message',
			'IMAP' => 'Internet Message Access Protocol',
			'IMHO' => 'In my humble opinion',
			'IMO' => 'In my opinion',
			'IOW' => 'In other words',
			'IP' => 'Internet Protocol',
			'IRC' => 'Internet Relay Chat',
			'IRL' => 'In real life',
			'ISO' => 'International Organization for Standardization',
			'ISP' => 'Internet Service Provider',
			'JDK' => 'Java Development Kit',
			'JPEG' => 'Joint Photographics Experts Group',
			'JPG' => 'Joint Photographics Experts Group',
			'JS' => 'JavaScript',
			'KB' => 'Kilobyte',
			'KISS' => 'Keep it simple, stupid',
			'LGPL' => 'GNU Lesser General Public License',
			'LOL' => 'Laughing out loud',
			'MB' => 'Megabyte',
			'MHz' => 'Megahertz',
			'MIME' => 'Multipurpose Internet Mail Extension',
			'MIT' => 'Massachusetts Institute of Technology',
			'MML' => 'Mathematical Markup Language',
			'MPEG' => 'Motion Picture Experts Group',
			'MS' => 'Microsoft',
			'MSDN' => 'Microsoft Developer Network',
			'MSIE' => 'Microsoft Internet Explorer',
			'MSN' => 'Microsoft Network',
			'OMG' => 'Oh my goodness',
			'OPML' => 'Outline Processor Markup Language',
			'OS' => 'Operating System',
			'OSS' => 'Open Source Software',
			'OTOH' => 'On the other hand',
			'P2P' => 'Peer to Peer',
			'PDA' => 'Personal Digital Assistant',
			'PDF' => 'Portable Document Format',
			'PHP' => 'Pre-Hypertext Processing',
			'PICS' => 'Platform for Internet Content Selection',
			'PIN' => 'Personal Identification Number',
			'PITA' => 'Pain in the Ass',
			'PNG' => 'Portable Network Graphics',
			'POP' => 'Post Office Protocol',
			'POP3' => 'Post Office Protocol 3',
			'Perl' => 'Practical Extraction and Report Language',
			'QoS' => 'Quality of Service',
			'RAID' => 'Redundant Array of Inexpensive Disks',
			'RDF' => 'Resource Description Framework',
			'ROFL' => 'Rolling on the floor laughing',
			'ROFLMAO' => 'Rolling on the floor laughing my ass of',
			'RPC' => 'Remote Procedure Call',
			'RSS' => 'Really Simple Syndication',
			'RTF' => 'Rich Text File',
			'RTFM' => 'Read The Fucking Manual',
			'SCSI' => 'Small Computer System Interface',
			'SDK' => 'Software Development Kit',
			'SGML' => 'Standard General Markup Language',
			'SMIL' => 'Synchronized Multimedia Integration Language',
			'SMTP' => 'Simple Mail Transfer Protocol',
			'SOAP' => 'Simple Object Access Protocol',
			'SQL' => 'Structured Query Language',
			'SSH' => 'Secure Shell',
			'SSI' => 'Server Side Includes',
			'SSL' => 'Secure Sockets Layer',
			'SVG' => 'Scalable Vector Graphics',
			'SVN' => 'Subversion',
			'TIA' => 'Thanks In Advance',
			'TIFF' => 'Tagged Image File Format',
			'TLD' => 'Top Level Domain',
			'TOC' => 'Table of Contents',
			'URI' => 'Uniform Resource Identifier',
			'URL' => 'Uniform Resource Locator',
			'URN' => 'Uniform Resource Name',
			'USB' => 'Universal Serial Bus',
			'VB' => 'Visual Basic',
			'VBA' => 'Visual Basic for Applications',
			'W3C' => 'World Wide Web Consortium',
			'WAN' => 'Wide Area Network',
			'WAP' => 'Wireless Access Protocol',
			'WML' => 'Wireless Markup Language',
			'WP' => 'WordPress',
			'WTF' => 'What the fuck',
			'WWW' => 'World Wide Web',
			'WYSIWYG' => 'What You See Is What You Get',
			'XHTML' => 'eXtensible HyperText Markup Language',
			'XML' => 'eXtensible Markup Language',
			'XSL' => 'eXtensible Stylesheet Language',
			'XSLT' => 'eXtensible Stylesheet Language Transformations',
			'XUL' => 'XML User Interface Language',
			'YMMV' => 'Your mileage may vary'
		);
		
		public function action_plugin_activation ( $file ) {
			
			$option = array();
			foreach ( $this->acronyms as $acronym => $text ) {
				$option[] = $acronym . '||' . $text;
			}
			
			$option = implode("\n", $option);
			
			Options::set( 'acronyms__acronyms', $option );
			
		}
		
		public function action_plugin_deactivation ( $file ) {
			
			Options::delete( 'acronyms__acronyms' );
			
		}
		
		public function filter_post_content_out ( $content, $post ) {
			
			$option = Options::get( 'acronyms__acronyms' );
			
			// if option is empty, populate it with the defaults
			if ( empty( $option ) ) {
				$this->action_plugin_activation(null);
				$option = $this->acronyms;
			}
			
			$option = explode( "\n", $option );
			
			$acronyms = array();
			foreach ( $option as $line ) {
				$line = explode( '||', $line );
				
				if ( count( $line ) < 2 ) {
					continue;
				}
				
				$acronyms[ $line[0] ] = $line[1];
			}
			
			$content = " $content ";
			foreach ( $acronyms as $acronym => $text ) {
								
				$content = preg_replace( "|(?!<[^<>]*?)(?<![?.&])\b$acronym\b(?!:)(?![^<>]*?>)|msU", "<abbr title=\"$text\">$acronym</abbr>" , $content );
				
			}
			$content = trim( $content );
			
			return $content;
			
		}
		
		public function configure() {
			
			$ui = new FormUI( 'acronyms' );
			
			$iam_key = $ui->append( 'textarea', 'acronyms', 'acronyms__acronyms', _t( 'Acronyms' ) );
			
			$ui->append( 'submit', 'save', _t( 'Save' ) );
			$ui->out();
			
		}

	}

?>
