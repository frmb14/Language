# Language
A PHP-class to render a specific language on the website depending on clients browser language for a multi-language website in Anax-MVC Framework: http://github.com/mosbth/Anax-MVC/

## How to use

### Installation
Install by using composer and enter the following code in your composer.json file:
```javascript
"require": {
	"frmb/language": "dev-master"
},
```
You can also clone this repository and install it manually in your Anax-MVC project. 

Once the files have been downloaded create a new folder in your Anax app/content directory called *Language*, this is where the XML files will be placed.

If you want to test the example included, copy the files from *vendor/frmb/language/content/Language* to your Language file created above and copy the **LanguageTest.php** file from the webroot directory to yours and you're ready to go!

### Usage

To load the Language Module you need to create a new Shared variable using this code in your Front Controller
```php
$di->setShared('language', function() use ($di){
    $language = new \Anax\Language\Language();
	$language->setDI($di);
    return $language;
});
```

Then you're ready to begin.

#### Basics
##### Configuration
Open up tile class file **Language.php** inside the installation directory (default *vendor/frmb/language/src/Language/*), inside the __construct function there's block of configuration. There you will set what languages that the site will support, en (English) should always be included as it's default for unsupported languages. Then whatever language you insert is up to you for example: **sv** (Swedish), **dk** (Denmark) and **no** (Norwegian), do remember that if you enter a language in the configuration, you MUST create a file matching the language.

To create new Language files use this template and call it **prefix_board.xml** where **prefix** is the language, for example **en** or **sv** inside the *content/Language* folder and paste this XML
```xml
<?xml version="1.0" encoding="utf-8"?>
<languagegroup>
	<lang>
		<word_app>board</word_app>
		<word_key>welcome_message</word_key>
		<word_default>Welcome to my module!</word_default>
	</lang>
	<lang>
		<word_app>board</word_app>
		<word_key>byline</word_key>
		<word_default>Powered by frmb/Language</word_default>
	</lang>
</languagegroup>
```

Having the Verbose option set to true is highly recommended during the development and testing period as you'll be able to see what files are being loaded and not. Verbose can also be toggled by writing `?verbose` in the browser.

##### General

The default file that's loaded when inside a Front Controller is the **prefix_board.xml** file where **prefix** is the selected language by the browser and automatically detected by Language (or manually by entering `?l=SHORT_LANGUAGE` in the browser), this is where all global words and sentences should be saved, such as credits, welcome message, footer messages and so on.

The class can accessed inside your Front Controller by typing
```php
$app->language->words('welcome_message'); 
```
This will generate the text from **prefix_board.xml** which is "Welcome to my module!" or any other language that you have set and translated.

To use the it inside a Controller or a Module class you will simply call the Words function by using $this instead of $app for example:
```php
class CommentController
{
	public function viewAction(){
		echo $this->language->words('commentcontroller_hello'); 
	}
}
```
But if we call the Language class inside a Controller or Module then we're no longer inside the "board", right? 
We're inside the Controller or Module and therefore we need a new XML document to cover its words!
As default you need to create a new file in your Language folder called **prefix_classname.xml**, in the example above the result would be **en_commentcontroller.xml**. You can simply paste the xml code that's in the Configuration a little bit up, but if you watch closely there's a xml object called "word_app" where it says "board", yet again, we're not in the board but in a Controller or a Module now, so change it to our class name, CommentController as in the exampel.

That should cover the most basic usage, more in Advanced usage!

### Advanced Usage

Lets continue on handling external Controllers like the example CommentController which I mentionened above. Sometimes you do not call a Controller or Module directly inside the Front Controller but still want to use the words for a Controller like our CommentController to write out a title or a sentence before it's been called.  
You can pre-load the controller file by passing the parameter "module" inside the `Words` function:

```php
$app->router->add('comment', function() use ($app) {
	$app->theme->setTitle($app->language->words( 'commentcontroller_hello', ['module' => 'CommentController']) );
	
	$app->views->add('comment/form', [
        'title' => , $app->language->words('commentcontroller_title'),
        'information' => $app->language->words('commentcontroller_information'),
    ]);
});
```
Now the CommentControllers xml file would have been loaded and is available for usage.

This can be useful for other things than only pre-loading, we can for example create a dynamic navbar
```php
'home'  => [
    'text'  => $this->di->language->words('navbar_home', ['module' => 'navbar']),
    'url'   => $this->di->get('url')->create(''),
    'title' => 'Home route of current frontcontroller'
],
'comments'  => [
    'text'  => $this->di->language->words('navbar_comments),
    'url'   => $this->di->get('url')->create('comments'),
    'title' => 'Route to our Comment Controllers'
],
```
Using the example above would require a new file called **prefix_navbar.xml** with the word_app set to navbar.

Forcing a language to be used is also possible, we can for example always make one set as English by using the parameter *lang*.

```php
$app->views->add('me/hem', [
	'content' => $content . $app->language->words('welcome_message') . '<br/>' . $app->language->words('welcome_message', ['lang' => 'en']),
	'byline' => $byline . $app->language->words('byline'),
]);
```

## Changelog
**2015-03-08** 1.0 - Initial release
