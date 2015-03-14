<?php

namespace Anax\Language;

/**
 * HTML Form elements.
 *
 */
class LanguageTest extends \PHPUnit_Framework_TestCase
{
	
	public function testWords()
	{

		$el = new \Anax\Language\Language();
		$di = new \Anax\DI\CDIFactoryDefault();
		$el->setDI($di);
 
		$res = $el->words('byline', ['lang' => 'en', 'module' => 'board']);
		$exp = 'Powered by Language module';
		$this->assertEquals($res, $exp, "Created element text missmatch.");
	}
}