<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	Nette\Utils\Strings;

/**
 * Base presenter for all application presenters.
 */
class BasePresenter extends \App\Module\Base\Presenters\BasePresenter
{
	/** @var Nette\Database\Context */
	public $database;

	public function injectRepository(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	protected function startup()
	{
		parent::startup();
	}

	public function generateFilenameUpload($filename){
		$ext = explode('.', $filename);
	    $ext = '.'.$ext[count($ext)-1];

	    $filename = substr($filename, 0, strlen($ext)*(-1)); //odstranění koncovky z názvu souboru
	    $filename = Strings::webalize($filename); //odstraněné speciálních znaků
	    $filename = substr($filename, 0, 235); //zkrácení názvu souboru, aby nedošlo k překročení limitu délky názvu
	    $filename = $filename.'-'.Strings::random(); //přidání random stringu
		return $filename.$ext;
	}

	public function imageGenerator($s, $crop = false, $w = 200, $h = 200, $id = 0){
		$imageGenerator = new \ImageGenerator();
		$imageGenerator->setUrl($s);
		$imageGenerator->setCropImage($crop);
		$imageGenerator->setWidth($w);
		$imageGenerator->setHeight($h);
        $imageGenerator->setId($id);
		return $imageGenerator->getUrlThumb();
	}

    /** locale control form control */
    protected function createComponentLocaleControlForm()
    {
        return new \App\Module\Admin\Controls\LocaleControlFormControl;
    }

    /** dropzone control */
    protected function createComponentDropzone()
    {
        $dropzone = new \App\AdminModule\Controls\DropzoneControl;
        return $dropzone;
    }
}
