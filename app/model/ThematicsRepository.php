<?php

declare(strict_types=1);

namespace App\Model;

use Nette;


class ThematicsRepository
{
	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	public function findAll(): Nette\Database\Table\Selection
	{
		return $this->database->table('thematics');
	}

	/** @return Nette\Database\Table\Selection */
	public function findAllTranslations($locale = 'cs'): Nette\Database\Table\Selection
	{
		$rows = $this->database->table('thematics_translations')->select('thematics.*, thematics_translations.*');
		if($locale!==null){
			$rows->where('locale', $locale);
		}

		return $rows;
	}

	public function findLocations(): Nette\Database\Table\Selection
	{
		return $this->database->table('thematics_locations');
	}

}