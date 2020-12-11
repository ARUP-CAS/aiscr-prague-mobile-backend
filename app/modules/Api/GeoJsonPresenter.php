<?php

declare(strict_types=1);

namespace App\Module\Api\Presenters;

use Nette,
	App\Model;

class GeoJsonPresenter extends \App\Module\Api\Presenters\BasePresenter
{
	/** @var Model\SettingsRepository */
	private $settings;

	public function __construct(Model\SettingsRepository $settings)
	{
		$this->settings = $settings;
	}

	public function actionDefault()
	{
		$method = $this->getHttpRequest()->getMethod();
		$headers = $this->getHttpRequest()->getHeaders();
		$baseUri = $this->link('//:Front:Default:', ['locale'=>null]);
		$locale = empty($locale) && isset($headers['accept-language']) ? substr($headers['accept-language'], 0, 2) : $locale;

		$settings = $this->settings->findAll()->get(1);
		
		$this->payload->url = $baseUri.'data/settings/'.$settings->value;
		$this->success();
	}

	public function actionPost()
	{
		$method = $this->getHttpRequest()->getMethod();

		$data = json_decode(file_get_contents('php://input'));

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