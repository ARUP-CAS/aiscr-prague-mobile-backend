<?php

declare(strict_types=1);

namespace App\Module\Api\Presenters;

use Nette,
	App\Model;

class ThematicsPresenter extends \App\Module\Api\Presenters\BasePresenter
{
	/** @var Model\LocationsRepository */
	private $locations;

	/** @var Model\ThematicsRepository */
	private $thematics;

	/** @var Model\SettingsRepository */
	private $settings;

	public function __construct(Model\LocationsRepository $locations, Model\ThematicsRepository $thematics, Model\SettingsRepository $settings)
	{
		$this->locations = $locations;
		$this->thematics = $thematics;
		$this->settings = $settings;
	}

	public function actionDefault($locale = 'cs', $id = 0)
	{
		$method = $this->getHttpRequest()->getMethod();
		$headers = $this->getHttpRequest()->getHeaders();
		$baseUri = $this->link('//:Front:Default:', ['locale'=>null]);
		$locale = empty($locale) && isset($headers['accept-language']) ? substr($headers['accept-language'], 0, 2) : $locale;

		$thematics = $this->thematics->findAllTranslations($locale)->where('show', 1);

		if($id>0){
			$thematics->where('id', $id);
		}

		$thematics_arr = [];
		foreach($thematics as $row){
			$locations = array_values($this->thematics->findLocations()->where('thematics_id', $row->id)->fetchPairs('locations_id', 'locations_id'));

			$thematics_arr[] = [
					'id' => $row->id,
					'title' => empty($row->title) ? null : $row->title,
					'latitude' => $row->latitude,
					'longitude' => $row->longitude,
					'locations' => $locations,
					'image' => !empty($row->image) ? $baseUri.'data/thematics/'.$row->image : '',
					'logo1' => !empty($row->logo_1) ? $baseUri.'data/thematics/'.$row->logo_1 : '',
					'logo2' => !empty($row->logo_2) ? $baseUri.'data/thematics/'.$row->logo_2 : '',
					'logo3' => !empty($row->logo_3) ? $baseUri.'data/thematics/'.$row->logo_3 : '',
					'logo4' => !empty($row->logo_4) ? $baseUri.'data/thematics/'.$row->logo_4 : '',
					'author' => empty($row->author) ? null : $row->author,
					'professionalCooperation' => empty($row->professional_cooperation) ? null : $row->professional_cooperation,
					'artisticsCooperation' => empty($row->artistics_cooperation) ? null : $row->artistics_cooperation,
					'thanks' => empty($row->thanks) ? null : $row->thanks,
					'characteristics' => empty($row->characteristics) ? null : $row->characteristics,
					'geoJson' => json_decode($row->geo_json),
				];
		}
		
		$this->payload->thematics = $thematics_arr;
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