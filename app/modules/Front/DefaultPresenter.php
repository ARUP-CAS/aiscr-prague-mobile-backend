<?php

declare(strict_types=1);

namespace App\Module\Front\Presenters;

use Nette;

class DefaultPresenter extends BasePresenter
{

	public function renderDefault(){
		$this->redirect(':Admin:Default:');
	}
}
