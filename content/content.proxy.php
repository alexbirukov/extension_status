<?php
    
	class contentExtensionExtension_statusProxy extends AJAXPage {
	
	    public static function normaliseVersionNumber($string) {
            $parts = explode('.', $string);
            return $string . str_repeat('.0', 3 - count($parts));
        }
	
		public function view() {
		    
		    header('Content-Type: text/xml');
		    $response = new XMLElement('response');
		    
		    $id = $_GET['id'];
		
			$version = Symphony::Configuration()->get('version', 'symphony');
			// remove text followed by numbers e.g. 2.3beta2 or 2.3rc1
			$version = preg_replace("/[a-z]+[0-9]+/i", '', $version);
			// remove text e.g. 2.3beta
			$version = preg_replace("/[a-z]+/i", '', $version);
			$symphony_version = self::normaliseVersionNumber($version);
			
			$response->setAttribute('symphony-version', $symphony_version);
		    
		    if(empty($id)) {
		        $response->setAttribute('error', '404');
		        echo $response->generate();die;
		    }
		    
		    $xml = @file_get_contents(sprintf('http://symphonyextensions.com:8080/api/extensions/%s/', $id));
		    
		    if(!$xml) {
		        $response->setAttribute('error', '404');
		        echo $response->generate();die;
		    }
		    		    
		    $extension = simplexml_load_string($xml);
		    $compatibility = $extension->xpath("//compatibility/symphony[@version='" . $symphony_version . "']");
		    
			$extensions = ExtensionManager::fetch();
			$current_version = $extensions[$id]['version'];
			
			$response->setAttribute('current-local-version', $current_version);
		
		    if(count($compatibility) == 0) {
		        $response->setAttribute('compatible-version-exists', 'no');
		    } else {
				$url = $extension->xpath("//link[@rel='github:page']/@href");
		        $response->setAttribute('compatible-version-exists', 'yes');
				$response->setAttribute('latest-url', (string)$url[0] . '/tree/' . $compatibility[0]->attributes()->use);
		        $response->setAttribute('latest', $compatibility[0]->attributes()->use);
		    }

		    echo $response->generate();die;
		    
		}
	
	}