<?php

declare(strict_types=1);

namespace App\Module\Api\Presenters;

use Nette;

class DefaultPresenter extends \App\Module\Api\Presenters\BasePresenter
{

	protected function startup()
	{
		parent::startup();
		/*if (!$this->user->isLoggedIn()) {
			$this->error("Unauthorized", Nette\Http\IResponse::S401_UNAUTHORIZED);
		}*/
	}

	public function actionDefault($email = null)
	{
		$this->error('Don\'t exists action');
	}

	public function actionPost()
	{
		$this->error('Don\'t exists action');
	}

	public function actionPut()
	{
		$this->error('Don\'t exists action');
	}

	public function actionDelete()
	{
		$this->error('Don\'t exists action');
	}
}