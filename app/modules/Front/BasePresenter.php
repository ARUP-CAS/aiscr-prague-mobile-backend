<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use App\Model,
	Nette;


class BasePresenter extends \App\Module\Base\Presenters\BasePresenter
{

	protected function startup()
	{
		parent::startup();
	}


	protected function beforeRender()
	{
		parent::beforeRender();
	}

	/**
	 * @param  string|NULL
	 * @return \Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = NULL): Nette\Application\UI\ITemplate
	{
		$template = parent::createTemplate($class);

		return $template;
	}
}	
