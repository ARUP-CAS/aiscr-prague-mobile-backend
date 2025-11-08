<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// absolute filesystem path to the web root
define('WWW_DIR', __DIR__);

App\Bootstrap::boot()
	->createContainer()
	->getByType(Nette\Application\Application::class)
	->run();
