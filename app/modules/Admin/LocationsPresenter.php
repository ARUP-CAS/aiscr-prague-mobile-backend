<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use App\Model,
	Nette,
	Nette\Application\UI\Form;

class LocationsPresenter extends \App\Module\Admin\Presenters\BasePresenter
{
	/** @var Model\LocationsRepository */
	private $locations;

	private $filePath = '/data/locations/';

	private $locationType = [
			'bojiste_mlade',
			'bojiste_stare',
			'dum',
			'hrad',
			'hradiste',
			'industrial',
			'judaismus',
			'klaster',
			'kostel',
			'most',
			'muzeum',
			'opevneni',
			'palac',
			'pametihodnost',
			'pohrebiste',
			'pravek',
			'rozhled',
			'stredovek',
			'studna',
			'tvrz',
			'usedlost',
			'vesnice',
			'vyhled',
			'zamek',
		];

	public function __construct(Model\LocationsRepository $locations)
	{
		$this->locations = $locations;
	}
	
	protected function startup()
	{
		parent::startup();

		if (!$this->user->isLoggedIn() || (!in_array("a", $this->user->getIdentity()->getRoles()) && !in_array("u", $this->user->getIdentity()->getRoles()))) {
			if ($this->user->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
				$this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
			}
			$this->getUser()->logout();
			$this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
		}
	}


	/********************* view default *********************/
	public function renderDefault()
	{
		$data = $this->locations->findAllTranslations('cs')->order('order, title');

		if(in_array("u", $this->user->getIdentity()->getRoles())){
			$data->where('user_add', $this->user->getId());
		}

		$this->template->data = $data;
	}

	public function renderPreview($id = 0){
		$data = json_decode(file_get_contents($this->link('//:Api:Location:').'?locale=cs&id='.$id));
		$location = $data->locations[0];

		$this->template->location = $location;
	}

    /** dropzone control */
    protected function createComponentDropzone()
    {
        $dropzone = new \App\AdminModule\Controls\DropzoneControl;
        return $dropzone;
    }

	public function renderAdd()
	{
		
	}

	public function renderEdit($id = 0)
	{
		$form = $this['locationForm'];
		if (!$form->isSubmitted()) {
			$row =  $this->locations->findAllTranslations('cs')->where('locations_id', $id);

			if(in_array("u", $this->user->getIdentity()->getRoles())){
				$row->where('user_add', $this->user->getId());
			}

			$row = $row->fetch();

			if (!$row) {
				$this->error('Záznam nenalezen!');
			}

			$row = $row->toArray();

			$form->setDefaults($row);
			$this->template->row = (object) $row;
			$this->template->sections  = $this->locations->findSections()->where('locations_id', $id)->order('order, id');
			/*$this->template->images = $this->locations->findImagesTranslations('cs')->where('locations_id', $row['id'])->order('sort ASC, id ASC');
			$this->template->files = $this->locations->findFiles()->where('locations_id', $row['id'])->order('sort ASC, id ASC');
			$this->template->videos = $this->locations->findVideos()->where('locations_id', $row['id'])->order('sort, id ASC');
			$this->template->models = $this->locations->findModels()->where('locations_id', $row['id'])->order('sort, id ASC');*/
		}
	}

	public function handleCreateSection($type = null){
		$id = (int) $this->getParameter('id');

		if($type=='text'){
			$text = $this->locations->findTexts()->insert([
				'locations_id' => $id,
			]);

			foreach($this->localeList as $locale){
				$this->locations->findTextsTranslations()->insert([
					'locations_texts_id' => $text->id,
					'locale' => $locale
				]);
			}

			$value = $text->id;
		}

		if($type=='images'){
			$images = $this->locations->findImages()->insert([
				'locations_id' => $id,
			]);

			$value = $images->id;
		}

		if($type=='videos'){
			$images = $this->locations->findVideos()->insert([
				'locations_id' => $id,
			]);

			$value = $images->id;
		}

		if($type=='models'){
			$models = $this->locations->findModels()->insert([
				'locations_id' => $id,
			]);

			$value = $models->id;
		}

		if($type=='files'){
			$files = $this->locations->findFiles()->insert([
				'locations_id' => $id,
			]);

			$value = $files->id;
		}

		if(isset($value)){
			$this->locations->findSections()->insert([
				'locations_id' => $id,
				'type' => $type,
				'value' => $value,
			]);
		}

		$this->redrawControl('sections');
	}

	public function actionDelete($id = 0)
	{
		if(!in_array("a", $this->user->getIdentity()->getRoles())){
			$this->flashMessage('Nemáte dostatečná oprávnění pro tuto akci.');
			$this->redirect('default');
		}

		$row = $this->locations->findAll()->get($id);
		if (!$row) {
			$this->flashMessage('Záznam nenalezen!');
		}else{
			$this->locations->findAll()->wherePrimary($id)->delete();
			
			$this->flashMessage('Záznam úspěšně smazán!');
		}

		$this->redirect('default');
	}

	public function actionDeleteSection($id = 0)
	{
		if(!in_array("a", $this->user->getIdentity()->getRoles())){
			$this->flashMessage('Nemáte dostatečná oprávnění pro tuto akci.');
			$this->redirect('default');
		}

		$row = $this->locations->findSections()->get($id);
		if (!$row) {
			$this->flashMessage('Záznam nenalezen!');
		}else{
			$this->locations->findSections()->wherePrimary($id)->delete();
			
			$this->flashMessage('Záznam úspěšně smazán!');
		}

		$this->redirect('default');
	}

	public function actionDeleteImage($id = 0)
	{
		$row = $this->locations->findImagesFiles()->get($id);
		$row2 = $this->locations->findImages()->get($row->locations_images_id);
		$location_id = $row2->locations_id;

		if (!$row) {
			$this->flashMessage('Záznam nenalezen!');
		}else{
			$this->flashMessage('Záznam úspěšně smazán!');
			$this->locations->findImagesFiles()->wherePrimary($id)->delete();
			
			if(is_file(WWW_DIR.$this->filePath . $row->filename))
				Nette\Utils\FileSystem::delete(WWW_DIR.$this->filePath . $row->filename);
		}

		$this->redirect('edit', $location_id);
	}

	public function handleDeleteFile($file_id)
	{
		if($file_id>0){
			$row = $this->locations->findFilesArs()->get($file_id);
			$row2 = $this->locations->findFiles()->get($row->locations_files_id);
			$location_id = $row2->locations_id;
			
			if (!$row) {
				$this->flashMessage('Záznam nenalezen!');
			}else{
				$this->flashMessage('Záznam úspěšně smazán!');
				$this->locations->findFilesArs()->wherePrimary($file_id)->delete();
				
				if(is_file(WWW_DIR.$this->filePath . $row->file_ios))
					Nette\Utils\FileSystem::delete(WWW_DIR.$this->filePath . $row->file_ios);

				if(is_file(WWW_DIR.$this->filePath . $row->file_android))
					Nette\Utils\FileSystem::delete(WWW_DIR.$this->filePath . $row->file_android);
			}
		}

		$this->redirect('this');
	}

	public function handleDeleteVideo($video_id)
	{
		if($video_id>0){
			$row = $this->locations->findVideosLinks()->get($video_id);
			$row2 = $this->locations->findVideos()->get($row->locations_videos_id);
			$location_id = $row2->locations_id;
			
			if (!$row) {
				$this->flashMessage('Záznam nenalezen!');
			}else{
				$this->flashMessage('Záznam úspěšně smazán!');
				$this->locations->findVideosLinks()->wherePrimary($video_id)->delete();
				
				if(is_file(WWW_DIR.$this->filePath . $row->image))
					Nette\Utils\FileSystem::delete(WWW_DIR.$this->filePath . $row->image);
			}
		}

		$this->redirect('this');
	}

	public function handleDeleteModel($model_id)
	{
		if($model_id>0){
			$row = $this->locations->findModelsFiles()->get($model_id);
			$row2 = $this->locations->findModels()->get($row->locations_models_id);
			$location_id = $row2->locations_id;
			
			if (!$row) {
				$this->flashMessage('Záznam nenalezen!');
			}else{
				$this->flashMessage('Záznam úspěšně smazán!');
				$this->locations->findModelsFiles()->wherePrimary($model_id)->delete();
				
				if(is_file(WWW_DIR.$this->filePath . $row->image))
					Nette\Utils\FileSystem::delete(WWW_DIR.$this->filePath . $row->image);

				if(is_file(WWW_DIR.$this->filePath . $row->file))
					Nette\Utils\FileSystem::delete(WWW_DIR.$this->filePath . $row->file);
			}
		}

		$this->redirect('this');
	}

	public function actionShow($id = 0){
		if(!in_array("a", $this->user->getIdentity()->getRoles())){
			$this->flashMessage('Nemáte dostatečná oprávnění pro tuto akci.');
			$this->redirect('default');
		}

		$row = $this->locations->findAll()->get($id);

		$this->locations->findAll()->where('id', $row->id)->update(array('show'=>$row->show ? 0 : 1));

		$this->flashMessage('Záznam byl úspěšně '.($row->show ? 'zneveřejněn' : 'zveřejněn').'.');
		$this->redirect('default');
	}

	public function actionUpdateSort($items){
        foreach(explode(',', $items) as $n => $row){
            $this->locations->findAll()->where('id', $row)->update(array('order'=>$n));
        }
        $this->redirect('default');
    }

	public function actionUpdateSortSections($items){
        foreach(explode(',', $items) as $n => $row){
            $this->locations->findSections()->where('id', $row)->update(array('order'=>$n));
        }
        $this->redirect('default');
    }

	public function actionUpdateSortImages($items){
        foreach(explode(',', $items) as $n => $row){
            $this->locations->findImagesFiles()->wherePrimary($row)->update(array('order'=>$n));
        }
        $this->redirect('default');
    }

    public function actionUpdateSortFiles($items){
        foreach(explode(',', $items) as $n => $row){
        	if(is_numeric($n)){
            	$this->locations->findFilesArs()->wherePrimary($row)->update(array('order'=>$n));
            }
        }
        $this->redirect('default');
    }

    public function actionUpdateSortVideos($items){
        foreach(explode(',', $items) as $n => $row){
        	if(is_numeric($n)){
            	$this->locations->findVideosLinks()->wherePrimary($row)->update(array('order'=>$n));
            }
        }
        $this->redirect('default');
    }

    public function actionUpdateSortModels($items){
        foreach(explode(',', $items) as $n => $row){
        	if(is_numeric($n)){
            	$this->locations->findModelsFiles()->wherePrimary($row)->update(array('order'=>$n));
            }
        }
        $this->redirect('default');
    }

    public function getDataParameters(){
    	$parameters = [];
    	$locations = $this->locations->findAllTranslations();
    	foreach($locations as $row){
    		$data = array_keys((array) json_decode($row->data));
    		$parameters = array_merge($parameters, $data);
    	}

    	return array_unique($parameters);
    	return [];
    }

    public function getDataByLocale($id = 0, $locale = 'cs'){
    	return $this->locations->findAllTranslations($locale)->where('locations_id', $id)->fetch();
    }

    public function getSectionImages($id = 0, $locale = 'cs'){
    	return $this->locations->findImagesFilesTranslations($locale)->where('locations_images_id', $id)->order('order, id');
    }

    public function getSectionVideos($id = 0, $locale = 'cs'){
    	return $this->locations->findVideosLinksTranslations($locale)->where('locations_videos_id', $id)->order('order, id');
    }

    public function getSectionFiles($id = 0, $locale = 'cs'){
    	return $this->locations->findFilesArsTranslations($locale)->where('locations_files_id', $id)->order('order, id');
    }

    public function getSectionModels($id = 0, $locale = 'cs'){
    	return $this->locations->findModelsFilesTranslations($locale)->where('locations_models_id', $id)->order('order, id');
    }

    /**
	 * Form factory.
	 * @return Form
	 */
	protected function createComponentCreateLocationForm()
	{
		$form = new Form;

		$form->addText('latitude', 'Latitude')
        	->setType('number')
        	->setRequired('Nebylo vyplněno pole %label.');

        $form->addText('longitude', 'Longitude')
        	->setType('number')
        	->setRequired('Nebylo vyplněno pole %label.');

        $form->addText('address', 'Adresa');

        $form->addSelect('type', 'Typ', array_combine($this->locationType, $this->locationType));

        $form->addUpload('image', 'Hlavní foto');

        $form->addSelect('open_time', 'Otevírací doba', [0=>'Ne', 1=>'Ano']);

        $form->addSelect('time_of_visit', 'Doba návštěvy', [0=>0, 15=>15, 30=>30, 45=>45, 60=>60]);

        $form->addSelect('availability', 'Dostupnost', ['snadná'=>'Snadná', 'střední'=>'Střední', 'obtížná'=>'Obtížná']);

		//submit as element BUTTON
		$form->addSubmit('save', 'Uložit');

		$form->onSuccess[] = [$this, 'createLocationFormSucceeded'];

		return $form;
	}


	public function createLocationFormSucceeded($form, $values)
	{
		$id = (int) $this->getParameter('id');
		$httpData = $form->getHttpData();

		$values->user_edit = $this->user->identity->id;
		$values->date_edit = new \DateTime;

		if(in_array("u", $this->user->getIdentity()->getRoles())){
			$values->show = 0;
		}

		/* file upload */
		$file = $values->image;
		if($file->isOk()!=''){ 
			$ext = explode('.', $file->getName());
		    $ext = '.'.$ext[count($ext)-1];
		    //$file_filename = substr(Nette\Utils\Strings::webalize($values->name), 0, 40).'-'.time(). $ext;
		    $file_filename = md5(time().rand()). $ext;
		    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
		    $file->move($imgUrl);
		    $values->image = $file_filename;
		}else{
			unset($values->image);
		}

	    if ($id) {
            $this->locations->findAll()->wherePrimary($id)->update($values);

            $this->flashMessage('Záznam byl úspěšně upraven.');
		} else {
			$values->user_add = $this->user->identity->id;
			$values->date_add = new \DateTime;

            $last = $this->locations->findAll()->insert($values);
            $id = $last->id;

			$this->flashMessage('Záznam byl úspěšně přidán.');
		}

        //translations title
		$httpData = $form->getHttpData();
		foreach($this->localeList as $locale){
			$data_translation['locations_id'] = $id;
			$data_translation['locale'] = $locale;
			$data_translation['title'] = isset($httpData['title']) ? $httpData['title'][$locale] : '';
			$data_translation['external_link'] = isset($httpData['external_link']) ? $httpData['external_link'][$locale] : '';

			$exist = $this->locations->findAllTranslations($locale)->where('locations_id', $id)->fetch();
			if($exist){
				$this->locations->findAllTranslations($locale)->where('locations_id', $id)->update($data_translation);
			}else{
				$this->locations->findAllTranslations($locale)->where('locations_id', $id)->insert($data_translation);
			}
		}

		$this->redirect('edit', $id);
	}

	/**
	 * Form factory.
	 * @return Form
	 */
	protected function createComponentLocationForm()
	{
		$form = new Form;

		$form->addText('latitude', 'Latitude')
        	->setType('number')
        	->setRequired('Nebylo vyplněno pole %label.');

        $form->addText('longitude', 'Longitude')
        	->setType('number')
        	->setRequired('Nebylo vyplněno pole %label.');

        $form->addText('address', 'Adresa');

        $form->addSelect('type', 'Typ', array_combine($this->locationType, $this->locationType));

        $form->addUpload('image', 'Hlavní foto')
        	->setAttribute('accept', '.jpg,.jpeg,.png');;

        $form->addSelect('open_time', 'Otevírací doba', [0=>'Ne', 1=>'Ano']);

        $form->addSelect('time_of_visit', 'Doba návštěvy', [0=>0, 15=>15, 30=>30, 45=>45, 60=>60]);

        $form->addSelect('availability', 'Dostupnost', ['snadná'=>'Snadná', 'střední'=>'Střední', 'obtížná'=>'Obtížná']);

		//submit as element BUTTON
		$form->addSubmit('save', 'Uložit');

		$form->onSuccess[] = [$this, 'locationFormSucceeded'];

		return $form;
	}


	public function locationFormSucceeded($form, $values)
	{
		$id = (int) $this->getParameter('id');
		$httpData = $form->getHttpData();

		$values->user_edit = $this->user->identity->id;
		$values->date_edit = new \DateTime;

		if(in_array("u", $this->user->getIdentity()->getRoles())){
			$values->show = 0;
		}

		/* file upload */
		$file = $values->image;
		if($file->isOk()!=''){ 
			$ext = explode('.', $file->getName());
		    $ext = '.'.$ext[count($ext)-1];
		    //$file_filename = substr(Nette\Utils\Strings::webalize($values->name), 0, 40).'-'.time(). $ext;
		    $file_filename = md5(time().rand()). $ext;
		    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
		    $file->move($imgUrl);
		    $values->image = $file_filename;
		}else{
			unset($values->image);
		}

	    if ($id) {
            $this->locations->findAll()->wherePrimary($id)->update($values);

            $this->flashMessage('Záznam byl úspěšně upraven.');
		} else {
			$values->user_add = $this->user->identity->id;
			$values->date_add = new \DateTime;

            $last = $this->locations->findAll()->insert($values);
            $id = $last->id;

			$this->flashMessage('Záznam byl úspěšně přidán.');
		}

        //translations title
		$httpData = $form->getHttpData();
		foreach($this->localeList as $locale){
			$data_translation['locations_id'] = $id;
			$data_translation['locale'] = $locale;
			$data_translation['title'] = isset($httpData['title']) ? $httpData['title'][$locale] : '';
			$data_translation['external_link'] = isset($httpData['external_link']) ? $httpData['external_link'][$locale] : '';

			$exist = $this->locations->findAllTranslations($locale)->where('locations_id', $id)->fetch();
			if($exist){
				$this->locations->findAllTranslations($locale)->where('locations_id', $id)->update($data_translation);
			}else{
				$this->locations->findAllTranslations($locale)->where('locations_id', $id)->insert($data_translation);
			}
		}

		//texts
		foreach ($httpData['texts'] as $text_id => $texts){
			foreach($texts as $locale => $text){
				$data_translation = [
						'text' => $text
					];

				$exist = $this->locations->findTextsTranslations($locale)->where('locations_texts_id', $text_id)->fetch();
				if($exist){
					$this->locations->findTextsTranslations($locale)->where('locations_texts_id', $text_id)->update($data_translation);
				}else{
					$this->locations->findTextsTranslations($locale)->where('locations_texts_id', $text_id)->insert($data_translation);
				}
			}
		}

		//images
		foreach($httpData as $n => $data){
			if(strpos($n, 'dropzone-images')!==false){
				$files = array_filter(explode(',', $httpData[$n]));
				$files_id = (int) explode('--', $n)[1];
				if($files_id>0){

					foreach($files as $file){
						$temp_dir = '/data/temp/';
						if(file_exists(WWW_DIR . $temp_dir . $file)){
							Nette\Utils\FileSystem::createDir(WWW_DIR . $this->filePath); //vytvoříme složku pokud neexistuje
							Nette\Utils\FileSystem::copy(WWW_DIR.$temp_dir.$file, WWW_DIR.$this->filePath.'/'.$file); //zkopírujeme soubor z tempu do správné složky
							Nette\Utils\FileSystem::delete(WWW_DIR.$temp_dir.$file);
							$image = $this->locations->findImagesFiles()->insert([
									'locations_images_id' => $files_id,
									'filename' => $file,
								]);

							$this->locations->findImagesFilesTranslations()->insert([
									'locations_images_files_id' => $image->id,
									'locale' => $this->localeList[0]
								]);
						}
					}
				}
			}
		}

		if(isset($httpData['images_texts'])){
			foreach($httpData['images_texts'] as $iid => $v){
				$this->locations->findImagesFilesTranslations(null)->where('locations_images_files_id', $iid)->delete();

				foreach($v as $loc => $value){
					$this->locations->findImagesFilesTranslations()->insert([
							'locations_images_files_id' => $iid,
							'text' => $value,
							'locale' => $loc,
						]);
				}
			}
		}

		//youtube videa
		if(isset($httpData['youtube'])){
			$sort = 0;
			foreach($httpData['youtube'] as $video_id => $youtube){
				foreach(array_filter($youtube) as $n => $row){
					if(!is_numeric($n)){
						$video = $this->locations->findVideosLinks()->insert([
								'locations_videos_id' => $video_id,
								'url' => $row,
								'order' => $sort,
							]);

						//file upload
						$file = $httpData['youtube_images'][$video_id][$n];
						if($file && $file->isOk()!=''){ 
							$ext = explode('.', $file->getName());
						    $ext = '.'.$ext[count($ext)-1];
						    $file_filename = substr(Nette\Utils\Strings::webalize($file->getName()), 0, 40).'-'.time(). $ext;
						    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
						    $file->move($imgUrl);
						    
						    $this->locations->findVideosLinks()->wherePrimary($video->id)->update([
									'image' => $file_filename,
								]);
						}

						foreach($httpData['youtube_texts'][$video_id][$n] as $loc => $value){
							$this->locations->findVideosLinksTranslations()->insert([
									'locations_videos_links_id' => $video->id,
									'text' => $value,
									'locale' => $loc,
								]);
						}
					}else{
						$this->locations->findVideosLinks()->wherePrimary($n)->update([
								'url' => $row,
								'order' => $sort,
							]);

						//file upload
						$file = $httpData['youtube_images'][$video_id][$n];
						if($file && $file->isOk()!=''){ 
							$ext = explode('.', $file->getName());
						    $ext = '.'.$ext[count($ext)-1];
						    $file_filename = substr(Nette\Utils\Strings::webalize($file->getName()), 0, 40).'-'.time(). $ext;
						    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
						    $file->move($imgUrl);
						    
						    $this->locations->findVideosLinks()->wherePrimary($n)->update([
									'image' => $file_filename,
								]);
						}

						foreach($httpData['youtube_texts'][$video_id][$n] as $loc => $value){
							$this->locations->findVideosLinksTranslations(null)->where('locations_videos_links_id', $n)->where('locale', $loc)->update([
									'text' => $value,
								]);
						}
					}

					$sort++;
				}
			}
		}

		//FILES ARS
		if(isset($httpData['files_files_ios'])){
			$sort = 0;
			foreach($httpData['files_files_ios'] as $file_id => $files){
				foreach($files as $n => $row){
					if(!is_numeric($n)){
						if(!empty($httpData['files_files_ios'][$file_id][$n])){
							$file_db = $this->locations->findFilesArs()->insert([
									'locations_files_id' => $file_id,
									'order' => $sort,
								]);

							//file upload
							$file = $httpData['files_files_ios'][$file_id][$n];
							if($file && $file->isOk()!=''){ 
								$ext = explode('.', $file->getName());
							    $ext = '.'.$ext[count($ext)-1];
							    $file_filename = substr(Nette\Utils\Strings::webalize($file->getName()), 0, 40).'-'.time(). $ext;
							    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
							    $file->move($imgUrl);
							    
							    $this->locations->findFilesArs()->wherePrimary($file_db->id)->update([
										'file_ios' => $file_filename,
									]);
							}

							//file upload
							$file = $httpData['files_files_android'][$file_id][$n];
							if($file && $file->isOk()!=''){ 
								$ext = explode('.', $file->getName());
							    $ext = '.'.$ext[count($ext)-1];
							    $file_filename = substr(Nette\Utils\Strings::webalize($file->getName()), 0, 40).'-'.time(). $ext;
							    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
							    $file->move($imgUrl);
							    
							    $this->locations->findFilesArs()->wherePrimary($file_db->id)->update([
										'file_android' => $file_filename,
									]);
							}

							foreach($httpData['files_texts'][$file_id][$n] as $loc => $value){
								$this->locations->findFilesArsTranslations()->insert([
										'locations_files_ars_id' => $file_db->id,
										'text' => $value,
										'locale' => $loc,
									]);
							}
						}
					}else{
						$this->locations->findFilesArs()->wherePrimary($n)->update([
								'order' => $sort,
							]);

						//file upload
						$file = $httpData['files_files_ios'][$file_id][$n];
						if($file && $file->isOk()!=''){ 
							$ext = explode('.', $file->getName());
						    $ext = '.'.$ext[count($ext)-1];
						    $file_filename = substr(Nette\Utils\Strings::webalize($file->getName()), 0, 40).'-'.time(). $ext;
						    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
						    $file->move($imgUrl);
						    
						    $this->locations->findFilesArs()->wherePrimary($n)->update([
									'file_ios' => $file_filename,
								]);
						}

						//file upload
						$file = $httpData['files_files_android'][$file_id][$n];
						if($file && $file->isOk()!=''){ 
							$ext = explode('.', $file->getName());
						    $ext = '.'.$ext[count($ext)-1];
						    $file_filename = substr(Nette\Utils\Strings::webalize($file->getName()), 0, 40).'-'.time(). $ext;
						    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
						    $file->move($imgUrl);
						    
						    $this->locations->findFilesArs()->wherePrimary($n)->update([
									'file_android' => $file_filename,
								]);
						}

						foreach($httpData['files_texts'][$file_id][$n] as $loc => $value){
							$this->locations->findFilesArsTranslations(null)->where('locations_files_ars_id', $n)->where('locale', $loc)->update([
									'text' => $value,
								]);
						}
					}

					$sort++;
				}
			}
		}

		//3D MODELS
		if(isset($httpData['models_files'])){
			$sort = 0;
			foreach($httpData['models_files'] as $file_id => $files){
				foreach($files as $n => $row){
					if(!is_numeric($n)){
						if(!empty($httpData['models_images'][$file_id][$n])){
							$model = $this->locations->findModelsFiles()->insert([
									'locations_models_id' => $file_id,
									'order' => $sort,
								]);

							//file upload
							$file = $httpData['models_images'][$file_id][$n];
							if($file && $file->isOk()!=''){ 
								$ext = explode('.', $file->getName());
							    $ext = '.'.$ext[count($ext)-1];
							    $file_filename = substr(Nette\Utils\Strings::webalize($file->getName()), 0, 40).'-'.time(). $ext;
							    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
							    $file->move($imgUrl);
							    
							    $this->locations->findModelsFiles()->wherePrimary($model->id)->update([
										'image' => $file_filename,
									]);
							}

							//file upload
							$file = $httpData['models_files'][$file_id][$n];
							if($file && $file->isOk()!=''){ 
								$ext = explode('.', $file->getName());
							    $ext = '.'.$ext[count($ext)-1];
							    $file_filename = substr(Nette\Utils\Strings::webalize($file->getName()), 0, 40).'-'.time(). $ext;
							    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
							    $file->move($imgUrl);
							    
							    $this->locations->findModelsFiles()->wherePrimary($model->id)->update([
										'file' => $file_filename,
									]);
							}

							foreach($httpData['models_texts'][$file_id][$n] as $loc => $value){
								$this->locations->findModelsFilesTranslations()->insert([
										'locations_models_files_id' => $model->id,
										'text' => $value,
										'locale' => $loc,
									]);
							}
						}
					}else{
						$this->locations->findModelsFiles()->wherePrimary($n)->update([
								'order' => $sort,
							]);

						//file upload
						$file = $httpData['models_images'][$file_id][$n];
						if($file && $file->isOk()!=''){ 
							$ext = explode('.', $file->getName());
						    $ext = '.'.$ext[count($ext)-1];
						    $file_filename = substr(Nette\Utils\Strings::webalize($file->getName()), 0, 40).'-'.time(). $ext;
						    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
						    $file->move($imgUrl);
						    
						    $this->locations->findModelsFiles()->wherePrimary($n)->update([
									'image' => $file_filename,
								]);
						}

						//file upload
						$file = $httpData['models_files'][$file_id][$n];
						if($file && $file->isOk()!=''){ 
							$ext = explode('.', $file->getName());
						    $ext = '.'.$ext[count($ext)-1];
						    $file_filename = substr(Nette\Utils\Strings::webalize($file->getName()), 0, 40).'-'.time(). $ext;
						    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
						    $file->move($imgUrl);
						    
						    $this->locations->findModelsFiles()->wherePrimary($n)->update([
									'file' => $file_filename,
								]);
						}

						foreach($httpData['models_texts'][$file_id][$n] as $loc => $value){
							$this->locations->findModelsFilesTranslations(null)->where('locations_models_files_id', $n)->where('locale', $loc)->update([
									'text' => $value,
								]);
						}
					}

					$sort++;
				}
			}
		}

		/*dump($httpData);
		exit;*/

        if ($id) {
			$this->redirect('edit', $id);
		} else {
			$this->redirect('default');
		}
	}
}
