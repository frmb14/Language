<?php

namespace Anax\Language;

/**
 * HTML Form elements.
 *
 */
class LanguageTest extends \PHPUnit_Framework_TestCase
{
	
	private $language;
	private $reflectionClass;
	
	public function setUp()
	{
		
		$this->language = new \Anax\Language\Language();
		$di = new \Anax\DI\CDIFactoryDefault();
		$this->language->setDI($di);
		
		//Create a reflection class so we can access Private and Protected items
		$this->reflectionClass = new \ReflectionClass("\Anax\Language\Language");
	}
	
	public function testConstructor()
	{

		//Get the property and make it accessable to us
		$property = $this->reflectionClass->getProperty('acceptedLangs');
		$property->setAccessible(true);

		$this->assertContains( "en" , $property->getValue($this->language));
		
		$property = $this->reflectionClass->getProperty('loaded');
		$property->setAccessible(true);

		$this->assertEquals( array() , $property->getValue($this->language));
	}

	
	public function testWords()
	{
		
		/*
		 * Try a word to make sure everything works in English
 		 */
		$res = $this->language->words('byline', ['lang' => 'en', 'module' => 'board']);
		$exp = 'Powered by Language module';
		$this->assertEquals($res, $exp, "Created element text missmatch.");
		
		/*
		 * Try a word to make sure everything works in Swedish
 		 */
		$res = $this->language->words('byline', ['lang' => 'sv', 'module' => 'board']);
		$exp = 'Driven av Language modulen';
		$this->assertEquals($res, $exp, "Created element text missmatch.");
	}
	
	public function testGetCallingFunction(){
		
		//$this->invokeMethod($user, 'cryptPassword', array('passwordToCrypt'));
		//$user->cryptPassword('passwordToCrypt');
		
		$res = $this->invokeMethod($this->language, 'get_calling_function');
		$exp = 'Anax\Language\LanguageTest';
		$this->assertEquals($res, $exp, "Called class text missmatch.");
	}
	
	public function testFindParent(){
		
		$this->language->words('byline', ['lang' => 'en', 'module' => 'board']);
		$property = $this->reflectionClass->getProperty('xml');
		$property->setAccessible(true);
		
		$xml = $property->getValue($this->language);
		
		$res = $this->invokeMethod($this->language, 'find_parent', array($xml['en_board']['lang'], 'byline'));
		$exp = '1';
		$this->assertEquals($res, $exp, "Expected array index 1");
	}
	
	public function testPreferedLanguage(){
		
		$res = $this->invokeMethod($this->language, 'prefered_language', array( array("en", "sv"), 'en,sv-SE;q=0.8,sv;q=0.5,en-US;q=0.3'));
		$this->assertContains( 'sv', key($res), "Text missmatch.");
	}
	
	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	public function invokeMethod(&$object, $methodName, array $parameters = array())
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}
}