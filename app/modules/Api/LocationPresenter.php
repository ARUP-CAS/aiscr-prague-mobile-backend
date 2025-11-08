<?php

declare(strict_types=1);

namespace App\Module\Api\Presenters;

use Nette,
	App\Model;

class LocationPresenter extends \App\Module\Api\Presenters\BasePresenter
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

		$locations = $this->locations->findAllTranslations($locale)->where('show', 1)->order('order, id');

		$this->payload->locale = $locale;

		if($id>0){
			$locations->where('id', $id);
		}

		$locations_arr = [];

		foreach($locations as $row){
			$content = [];

			$sections = $this->locations->findSections()->where('locations_id', $row->id)->order('order, id');
			foreach($sections as $section){
				$item_content = null;

				if($section->type=='text'){
					$text = $this->locations->findTextsTranslations($locale)->where('locations_texts_id', $section->value)->fetch();
					$item_content = [['text'=>$text->text]];
				}

				if($section->type=='images'){
					$item_content = [];
					$images = $this->locations->findImagesFilesTranslations($locale)->where('locations_images_id', $section->value)->order('order, id');
					foreach($images as $res){
						$item_content[] = [
							'url' => $baseUri.'data/locations/'.$res->filename,
							'sort' => $res->order,
							'text' => $res->text
						];
					}
				}

				if($section->type=='videos'){
					$item_content = [];
					$files = $this->locations->findVideosLinksTranslations($locale)->where('locations_videos_id', $section->value)->order('order, id');
					foreach($files as $res){
						$item_content[] = [
								'urlVideo' => $res->url,
								'urlImage' => $baseUri.'data/locations/'.$res->image,
								'sort' => $res->order,
								'text' => $res->text
							];
					}
				}

				if($section->type=='files'){
					$item_content = [];
					$files = $this->locations->findFilesArsTranslations($locale)->where('locations_files_id', $section->value)->order('order, id');
					foreach($files as $res){
						$item_content[] = [
							'urlIos' => $baseUri.'data/locations/'.$res->file_ios,
							'urlAndroid' => $baseUri.'data/locations/'.$res->file_android,
							'sort' => $res->order,
							'text' => $res->text
						];
					}
				}

				if($section->type=='models'){
					$item_content = [];
					$files = $this->locations->findModelsFilesTranslations($locale)->where('locations_models_id', $section->value)->order('order, id');
					foreach($files as $res){
						$item_content[] = [
								'urlFile' => $baseUri.'data/locations/'.$res->file,
								'urlImage' => $baseUri.'data/locations/'.$res->image,
								'sort' => $res->order,
								'text' => $res->text
							];
					}
				}


				$content[] = [
						'type' => $section->type,
						'content' => $item_content,
					];
			}

			$thematics_arr = [];
			$thematics_locations = $this->thematics->findLocations()->where('locations_id', $row->id)->fetchPairs('thematics_id', 'thematics_id');
			/*$thematics = $this->thematics->findAllTranslations($locale)->where('id', $thematics_locations);
			foreach($thematics as $res){
				$thematics_arr[] = [
						'id' => $res->id,
						'title' => $res->title,
						'latitude' => $res->latitude,
						'longitude' => $res->longitude,
						'image' => !empty($res->image) ? $baseUri.'data/thematics/'.$res->image : '',
						'logo1' => !empty($res->logo_1) ? $baseUri.'data/thematics/'.$res->logo_1 : '',
						'logo2' => !empty($res->logo_2) ? $baseUri.'data/thematics/'.$res->logo_2 : '',
						'logo3' => !empty($res->logo_3) ? $baseUri.'data/thematics/'.$res->logo_3 : '',
						'logo4' => !empty($res->logo_4) ? $baseUri.'data/thematics/'.$res->logo_4 : '',
						'author' => $res->author,
						'professionalCooperation' => $res->professional_cooperation,
						'artisticsCooperation' => $res->artistics_cooperation,
						'thanks' => $res->thanks,
					];
			}*/

			$locations_arr[] = [
					'id' => $row->id,
					'title' => $row->title,
					'type' => $row->type,
					'latitude' => $row->latitude,
					'longitude' => $row->longitude,
					'address' => $row->address,
					'externalLink' => empty($row->external_link) ? null : $row->external_link,
					'image' => !empty($row->image) ? $baseUri.'data/locations/'.$row->image : '',
					'openTime' => $row->open_time ? true : false,
					'timeOfVisit' => $row->time_of_visit,
					'availability' => str_replace(['snadná', 'střední', 'obtížná'], ['easy', 'good', 'hard'], $row->availability),
					'content' => $content,
					'thematics' => array_values($thematics_locations),
				];
		}

		$this->payload->locations = $locations_arr;
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
