<?php

namespace Anax\Language;

class Language implements \Anax\DI\IInjectionAware
{
    use \Anax\DI\TInjectable;
	
	private $xml;
	private $lang;
	private $class;
	private $loaded;
	private $verbose;
	private $langPath;
	private $customModule;
	private $acceptedLangs;
	
    /**
     * Constructor, setting variables and configuration
     * 
     */
	public function __construct(){
		
		/**
		 *  Configuration
		 */
		$this->acceptedLangs = array("en", "sv"); // The languages accepted and available in the path set below
		$this->langPath = "content/Language/"; 
		$this->verbose = false; // Show debug information, true / false
		
		
		// Loading the variables
		$this->xml = [];
		$this->loaded = [];
		$this->customModule = [];
		
	}
	
    /**
     * The main function that grabs the sentence from the correct XML document depending on the browser language and / or set language.
     * 
     * @param int $input key identifier for the XML document
     * @param array $options user options of various kind, 'lang' => 'en' for manual English language and 'module' => 'XController' for custom module loading in the VIEWS (this is not needed when called from the actual module)
     * 
     * @return string $word, the full sentence / word from said key
     */
	public function words($input=null, $options=[]){
		
		if(isset($_GET['verbose'])) $this->verbose = true;
		
		// Using the Get variable 'L' in the browser will force all languages to set language if it's in the AcceptedLangs variable
		if($this->request->getGet('l') && in_array($setLang = strtolower($this->request->getGet('l')), $this->acceptedLangs)){
			$this->lang = $setLang;
		}
		else{
			if(array_key_exists('lang' , $options) && in_array($options['lang'], $this->acceptedLangs)){
				$this->lang = $options['lang'];
			}
			else{
				$this->lang = key ($this->prefered_language($this->acceptedLangs, $_SERVER["HTTP_ACCEPT_LANGUAGE"]));
			}
		}
		
		if(!array_key_exists('module' , $options)){
			$this->class = @end( explode( '\\', strtolower( @$this->get_calling_function() ) ) );
			if($this->class == '') $this->class = 'board';
			
		}
		else{
			if(!in_array(strtolower($options['module']), $this->customModule)){
				array_push($this->customModule, strtolower($options['module']));
				//$this->customModule = strtolower($options['module']);
				$this->class = end($this->customModule);
				if($this->verbose) echo "<pre><b>CUSTOM Module: </b>" . $this->lang . '_' . $this->class . '</pre>';
			}
		}
		
		$moduleKey = $this->lang.'_'.$this->class;
		
		if(!in_array($moduleKey, $this->loaded))
			$this->loadXML();
		
		
		$moduleLoaded = [];
		foreach($this->loaded as $key => $value){
			if(array_key_exists($value, $this->xml)){
				$moduleLoaded[$value] = true;
				continue;
			}
		}
		
		if( array_key_exists($moduleKey , $moduleLoaded ) && $moduleLoaded[$moduleKey] === true ){
			if(!is_null( $index = $this->find_parent( $this->xml[$moduleKey]['lang'], $input )))
				$word = $this->xml[$moduleKey]['lang'][$index]['word_default'];
			else{
				foreach($this->customModule as $key => $value){
					if(!is_null( $index = $this->find_parent( $this->xml[$this->lang.'_'.$value]['lang'], $input )))
						break;
				}
				if(!is_null($index)){
					$word = $this->xml[$this->lang.'_'.$value]['lang'][$index]['word_default'];
				}
				else{
					if($this->verbose) echo "<pre><b>NOTICE</b>: The word '<b>{$input}</b>' does not exist in any of the loaded modules!</pre>";
					return;
				}
			}
			$word = str_replace( "\n", '<br />', $word );
			
			return $word;
		}
		else{
			// This should never occur unless something is wrong
			if($this->verbose) echo "<pre><b>Module not loaded yet</b>: " . $this->lang . "_" . $this->class . ".xml</pre>";
		}
	}
	
    /**
     * Loads the XML file for the current module and language
     * 
     * 
     * @return void
     */
	private function loadXML(){
		
		$xml = simplexml_load_file( ANAX_APP_PATH . $this->langPath . $this->lang . '_' . $this->class . '.xml') or printf("<pre>No such file: " . ANAX_APP_PATH . $this->langPath . $this->lang . '_' . $this->class . '.xml</pre>');
		if($this->verbose) echo "<pre><b>Module loaded: </b>" . ANAX_APP_PATH . $this->langPath . $this->lang . '_' . $this->class . '.xml </pre>';
		//Convert from Simple XML Object to array
		$xml = json_decode(json_encode((array)$xml), TRUE);
		$this->xml[$this->lang.'_'.strtolower( $xml['lang'][0]['word_app'] )] = $xml;
		
		//Set module as loaded
		array_push($this->loaded, $this->lang.'_'.$this->class);
	}
	
    /**
     * 
     * 
     * @param array $available_languages 
     * @param array $http_accept_language 
     * 
     * @return array with available languages
     */
	private function prefered_language(array $available_languages, $http_accept_language){
		
		$available_languages = array_flip($available_languages);
		$langs = array();
		preg_match_all('~([\w-]+)(?:[^,\d]+([\d.]+))?~', strtolower($http_accept_language), $matches, PREG_SET_ORDER);
		foreach($matches as $match){
			list($a, ) = explode('-', $match[1]) + array('', '');
			$value = isset($match[2]) ? (float) $match[2] : 1.0;

			if(isset($available_languages[$match[1]])) {
				$langs[$match[1]] = $value;
				continue;
			}
			if(isset($available_languages[$a])) {
				$langs[$a] = $value - 0.1;
			}
		}
		arsort($langs);
		return $langs;
	}
	
    /**
     * Find the parent array index key using a search word
     * 
     * @param array $array 
     * @param str $find 
     * 
     * @return int array key for parent
     */
	private function find_parent($array, $find){
		foreach($array as $key => $value) {
			if(in_array($find, $value)) return $key;
		}
	}
	
    /**
     * Returns the class name where this class have been called from
     * 
     * 
     * @return string
     */
	private function get_calling_function(){
		$caller = debug_backtrace();
		$caller = $caller[2];
		$r = null;
			if (isset($caller['class'])){
			$r = $caller['class'];
		}
		if (isset($caller['object'])){
			if($r != get_class($caller['object'])){
				$r = get_class($caller['object']);
			}
		}
		return $r;
	}
}
