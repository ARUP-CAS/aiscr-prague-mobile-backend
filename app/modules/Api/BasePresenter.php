<?php

declare(strict_types=1);

namespace App\Module\Api\Presenters;

use App\Model,
	Nette,
	Nette\Http\IRequest,
	Nette\Http\IResponse;

class BasePresenter extends \App\Module\Base\Presenters\BasePresenter
{

	protected function startup()
	{
		parent::startup();
	}

	/**
	 * @param  string|NULL
	 * @return \Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = null): Nette\Application\UI\ITemplate
	{
		$template = parent::createTemplate($class);

		return $template;
	}

	public function success($status = 'ok', $httpCode = IResponse::S200_OK): void
	{
		$this->getHttpResponse()->setCode($httpCode);
		$this->payload->status = $status;
		$this->payload->statusCode = $httpCode;
		$this->sendPayload();
	}

	public function error($error = NULL, $httpCode = IResponse::S406_NOT_ACCEPTABLE): void
	{
		$this->getHttpResponse()->setCode($httpCode);
		$this->payload->error = [
			'message' => $error,
		];
		$this->payload->status = 'error';
		$this->payload->statusCode = $httpCode;
		$this->sendPayload();
	}
}	
