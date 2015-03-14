<?php

// Get environment & autoloader.
require __DIR__.'/config.php';

// Create services and inject into the app. 
$di  = new \Anax\DI\CDIFactoryDefault();
$app = new \Anax\Kernel\CAnax($di);

$di->setShared('language', function() use ($di){
    $language = new \Anax\Language\Language();
	$language->setDI($di);
    return $language;
});

$app->router->add('', function() use ($app){
	$app->theme->setTitle($app->language->words('title'));
	
    $app->views->add('default/page', [
	
		'title'	=> $app->language->words('title'),
        'content' => $app->language->words('welcome_message') . '<br/>' . 
		'<small>' . $app->language->words('created_by') .'</small>'. 
		'<p>' . $app->language->words('select_language') . '</p>',
		'links' => [
            [
                'href' => $app->url->create('?l=sv'),
                'text' => $app->language->words('swedish'),
            ],
			[
                'href' => $app->url->create('?l=en'),
                'text' => $app->language->words('english'),
            ],
        ],
		
    ]);
	
});


$app->router->add('source', function() use ($app){
	$app->theme->addStylesheet('css/source.css');
    $app->theme->setTitle("Källkod");
 
    $source = new \Mos\Source\CSource([
        'secure_dir' => '..', 
        'base_dir' => '..', 
        'add_ignore' => ['.htaccess'],
    ]);
 
    $app->views->add('me/source', [
        'content' => $source->View(),
    ]);
});

$app->router->handle();
$app->theme->render();