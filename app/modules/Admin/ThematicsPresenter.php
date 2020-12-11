<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use App\Model,
	Nette,
	Nette\Application\UI\Form;

class ThematicsPresenter extends \App\Module\Admin\Presenters\BasePresenter
{
	/** @var Model\LocationsRepository */
	private $locations;

	/** @var Model\ThematicsRepository */
	private $thematics;

	private $filePath = '/data/thematics/';

	public function __construct(Model\LocationsRepository $locations, Model\ThematicsRepository $thematics)
	{
		$this->locations = $locations;
		$this->thematics = $thematics;
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
		$data = $this->thematics->findAllTranslations('cs')->order('title');

		if(in_array("u", $this->user->getIdentity()->getRoles())){
			$data->where('user_add', $this->user->getId());
		}

		$this->template->data = $data;
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
		$form = $this['thematicForm'];
		if (!$form->isSubmitted()) {
			$row =  $this->thematics->findAllTranslations('cs')->where('thematics_id', $id);

			if(in_array("u", $this->user->getIdentity()->getRoles())){
				$row->where('user_add', $this->user->getId());
			}

			$row = $row->fetch();

			if (!$row) {
				$this->error('Záznam nenalezen!');
			}		

			$row = $row->toArray();

			$row['locations_ids'] = array_filter($this->thematics->findLocations()->where('thematics_id', $id)->fetchPairs('locations_id', 'locations_id'));

			$form->setDefaults($row);
			$this->template->row = (object) $row;
		}
	}

	public function actionDelete($id = 0)
	{
		if(!in_array("a", $this->user->getIdentity()->getRoles())){
			$this->flashMessage('Nemáte dostatečná oprávnění pro tuto akci.');
			$this->redirect('default');
		}

		$row = $this->thematics->findAll()->get($id);
		if (!$row) {
			$this->flashMessage('Záznam nenalezen!');
		}else{
			$this->thematics->findAll()->wherePrimary($id)->delete();
			
			$this->flashMessage('Záznam úspěšně smazán!');
		}

		$this->redirect('default');
	}

	public function actionShow($id = 0){
		if(!in_array("a", $this->user->getIdentity()->getRoles())){
			$this->flashMessage('Nemáte dostatečná oprávnění pro tuto akci.');
			$this->redirect('default');
		}

		$row = $this->thematics->findAll()->get($id);

		$this->thematics->findAll()->where('id', $row->id)->update(array('show'=>$row->show ? 0 : 1));

		$this->flashMessage('Záznam byl úspěšně '.($row->show ? 'zneveřejněn' : 'zveřejněn').'.');
		$this->redirect('default');
	}

	public function actionUpdateSort($items){
        foreach(explode(',', $items) as $n => $row){
            $this->locations->findAll()->where('id', $row)->update(array('order'=>$n));
        }
        $this->redirect('default');
    }

	/**
	 * Form factory.
	 * @return Form
	 */
	protected function createComponentThematicForm()
	{
		$form = new Form;

		$locations = $this->locations->findAllTranslations('cs');
		if(in_array("u", $this->user->getIdentity()->getRoles())){
			$locations->where('user_add', $this->user->getId());
		}
		$locations = $locations->fetchPairs('id', 'title');
		$form->addMultiselect('locations_ids', 'Lokality', $locations);

		$form->addText('latitude', 'Latitude')
        	->setType('number')
        	->setRequired('Nebylo vyplněno pole %label.');

        $form->addText('longitude', 'Longitude')
        	->setType('number')
        	->setRequired('Nebylo vyplněno pole %label.');

        $form->addUpload('image', 'Hlavní foto')
        	->setAttribute('accept', '.jpg,.jpeg,.png');
        $form->addUpload('logo_1', 'Logo 1')
        	->setAttribute('accept', '.jpg,.jpeg,.png');
        $form->addUpload('logo_2', 'Logo 2')
        	->setAttribute('accept', '.jpg,.jpeg,.png');
        $form->addUpload('logo_3', 'Logo 3')
        	->setAttribute('accept', '.jpg,.jpeg,.png');
        $form->addUpload('logo_4', 'Logo 4')
        	->setAttribute('accept', '.jpg,.jpeg,.png');

		//submit as element BUTTON
		$form->addSubmit('save', 'Uložit');

		$form->onSuccess[] = [$this, 'thematicFormSucceeded'];

		return $form;
	}


	public function thematicFormSucceeded($form, $values)
	{
		$id = (int) $this->getParameter('id');
		$httpData = $form->getHttpData();

		$values->user_edit = $this->user->identity->id;
		$values->date_edit = new \DateTime;

		if(in_array("u", $this->user->getIdentity()->getRoles())){
			$values->show = 0;
		}

		$locations = $values->locations_ids;
		unset($values->locations_ids);

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

		/* file upload */
		$file = $values->logo_1;
		if($file->isOk()!=''){ 
			$ext = explode('.', $file->getName());
		    $ext = '.'.$ext[count($ext)-1];
		    //$file_filename = substr(Nette\Utils\Strings::webalize($values->name), 0, 40).'-'.time(). $ext;
		    $file_filename = md5(time().rand()). $ext;
		    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
		    $file->move($imgUrl);
		    $values->logo_1 = $file_filename;
		}else{
			unset($values->logo_1);
		}

		/* file upload */
		$file = $values->logo_2;
		if($file->isOk()!=''){ 
			$ext = explode('.', $file->getName());
		    $ext = '.'.$ext[count($ext)-1];
		    //$file_filename = substr(Nette\Utils\Strings::webalize($values->name), 0, 40).'-'.time(). $ext;
		    $file_filename = md5(time().rand()). $ext;
		    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
		    $file->move($imgUrl);
		    $values->logo_2 = $file_filename;
		}else{
			unset($values->logo_2);
		}

		/* file upload */
		$file = $values->logo_3;
		if($file->isOk()!=''){ 
			$ext = explode('.', $file->getName());
		    $ext = '.'.$ext[count($ext)-1];
		    //$file_filename = substr(Nette\Utils\Strings::webalize($values->name), 0, 40).'-'.time(). $ext;
		    $file_filename = md5(time().rand()). $ext;
		    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
		    $file->move($imgUrl);
		    $values->logo_3 = $file_filename;
		}else{
			unset($values->logo_3);
		}

		/* file upload */
		$file = $values->logo_4;
		if($file->isOk()!=''){ 
			$ext = explode('.', $file->getName());
		    $ext = '.'.$ext[count($ext)-1];
		    //$file_filename = substr(Nette\Utils\Strings::webalize($values->name), 0, 40).'-'.time(). $ext;
		    $file_filename = md5(time().rand()). $ext;
		    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
		    $file->move($imgUrl);
		    $values->logo_4 = $file_filename;
		}else{
			unset($values->logo_4);
		}

	    if ($id) {
            $this->thematics->findAll()->wherePrimary($id)->update($values);

            $this->flashMessage('Záznam byl úspěšně upraven.');
		} else {
			$values->user_add = $this->user->identity->id;
			$values->date_add = new \DateTime;

            $last = $this->thematics->findAll()->insert($values);
            $id = $last->id;

			$this->flashMessage('Záznam byl úspěšně přidán.');
		}

        //translations title
		$httpData = $form->getHttpData();
		foreach($this->localeList as $locale){
			$data_translation['thematics_id'] = $id;
			$data_translation['locale'] = $locale;
			$data_translation['title'] = isset($httpData['title']) ? $httpData['title'][$locale] : '';
			$data_translation['author'] = isset($httpData['author']) ? $httpData['author'][$locale] : '';
			$data_translation['professional_cooperation'] = isset($httpData['professional_cooperation']) ? $httpData['professional_cooperation'][$locale] : '';
			$data_translation['artistics_cooperation'] = isset($httpData['artistics_cooperation']) ? $httpData['artistics_cooperation'][$locale] : '';
			$data_translation['thanks'] = isset($httpData['thanks']) ? $httpData['thanks'][$locale] : '';
			$data_translation['characteristics'] = isset($httpData['characteristics']) ? $httpData['characteristics'][$locale] : '';

			$exist = $this->thematics->findAllTranslations($locale)->where('thematics_id', $id)->fetch();
			if($exist){
				$this->thematics->findAllTranslations($locale)->where('thematics_id', $id)->update($data_translation);
			}else{
				$this->thematics->findAllTranslations($locale)->where('thematics_id', $id)->insert($data_translation);
			}
		}

		$this->thematics->findLocations()->where('thematics_id', $id)->delete();
		foreach($locations as $loc){
			$this->thematics->findLocations()->insert([
					'thematics_id' => $id,
					'locations_id' => $loc,
				]);
		}

        if ($id) {
			$this->redirect('edit', $id);
		} else {
			$this->redirect('default');
		}
	}
}
