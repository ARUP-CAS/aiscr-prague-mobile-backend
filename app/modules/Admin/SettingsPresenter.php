<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use App\Model,
	Nette,
	Nette\Application\UI\Form;

class SettingsPresenter extends \App\Module\Admin\Presenters\BasePresenter
{
	/** @var Model\SettingsRepository */
	private $settings;

	/** @var Model\ThematicsRepository */
	private $thematics;

	public $filePath = '/data/settings/';

	public function __construct(Model\SettingsRepository $settings, Model\ThematicsRepository $thematics)
	{
		$this->settings = $settings;
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

	public function renderDefault()
	{
		$form = $this['settingForm'];
		if (!$form->isSubmitted()) {
			$row =  $this->settings->findAll()->fetch();

			if (!$row) {
				$this->error('Záznam nenalezen!');
			}		

			$form->setDefaults($row);
			$this->template->row = (object) ['geo_json'=>$row->value];
		}

		$dir = WWW_DIR.$this->filePath;
		$files = array_slice(array_diff(scandir($dir,SCANDIR_SORT_DESCENDING), array('..', '.')), 0, 50);
		$files_out = [];
		foreach($files as $row){
			$files_out[$row] = filemtime($dir.$row); 
		}
		arsort($files_out);
		$this->template->files = $files_out;
		//$this->template->files = ;
	}

	/**
	 * Form factory.
	 * @return Form
	 */
	protected function createComponentSettingForm()
	{
		$form = new Form;

        $form->addUpload('geo_json', 'GEO JSON')
        	->setAttribute('accept', 'application/json');

		//submit as element BUTTON
		$form->addSubmit('save', 'Uložit');

		$form->onSuccess[] = [$this, 'settingFormSucceeded'];

		return $form;
	}


	public function settingFormSucceeded($form, $values)
	{
		$id = 1;

		/* file upload */
		$file = $values->geo_json;
		if($file->isOk()!=''){ 
			$ext = explode('.', $file->getName());
		    $ext = '.'.$ext[count($ext)-1];
		    //$file_filename = substr(Nette\Utils\Strings::webalize($values->name), 0, 40).'-'.time(). $ext;
		    $file_filename = md5(time().rand()). $ext;
		    $imgUrl = WWW_DIR.$this->filePath . $file_filename;
		    $file->move($imgUrl);
		    $values->geo_json = $file_filename;
		}else{
			$values->geo_json = '';
		}

	    $this->settings->findAll()->wherePrimary($id)->update([
        		'value' => $values->geo_json,    	
            ]);

	    if(isset($imgUrl)){
	    	$this->thematics->findAll()->update([
					'geo_json' => ''
				]);

	    	$geo_json = json_decode(file_get_contents($imgUrl));
	    	if($geo_json!==null){
	    		foreach($geo_json->features as $row){
	    			if($row->properties && isset($row->properties->{'topic-id'})){
	    				$this->thematics->findAll()->wherePrimary($row->properties->{'topic-id'})->update([
	    						'geo_json' => json_encode($row)
	    					]);
	    			}
	    		}
	    	}
	    }

        $this->flashMessage('Záznam byl úspěšně upraven.');

		$this->redirect('default');
	}
}
