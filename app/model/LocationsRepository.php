<?php

declare(strict_types=1);

namespace App\Model;

use Nette;


class LocationsRepository
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
		return $this->database->table('locations');
	}

	/** @return Nette\Database\Table\Selection */
	public function findTranslation(int $id): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_translations')->where('locations_id', $id);
	}

	/** @return Nette\Database\Table\Selection */
	public function findAllTranslations($locale = 'cs'): Nette\Database\Table\Selection
	{
		$rows = $this->database->table('locations_translations')->select('locations.*, locations_translations.*');
		if($locale!==null){
			$rows->where('locale', $locale);
		}

		return $rows;
	}

	/** @return Nette\Database\Table\Selection */
	public function findSections(): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_sections');
	}

	/** @return Nette\Database\Table\Selection */
	public function findTexts(): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_texts');
	}

	/** @return Nette\Database\Table\Selection */
	public function findTextsTranslations($locale = 'cs'): Nette\Database\Table\Selection
	{
		$rows = $this->database->table('locations_texts_translations')->select('locations_texts.*, locations_texts_translations.*');
		if($locale!==null){
			$rows->where('locale', $locale);
		}

		return $rows;
	}

	/** @return Nette\Database\Table\Selection */
	public function findImages(): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_images');
	}

	/** @return Nette\Database\Table\Selection */
	public function findImagesFiles(): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_images_files');
	}

	/** @return Nette\Database\Table\Selection */
	public function findImagesFilesTranslations($locale = 'cs'): Nette\Database\Table\Selection
	{
		$rows = $this->database->table('locations_images_files_translations')->select('locations_images_files.*, locations_images_files_translations.*');
		if($locale!==null){
			$rows->where('locale', $locale);
		}

		return $rows;
	}

	/** @return Nette\Database\Table\Selection */
	public function findVideos(): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_videos');
	}

	/** @return Nette\Database\Table\Selection */
	public function findVideosLinks(): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_videos_links');
	}

	/** @return Nette\Database\Table\Selection */
	public function findVideosLinksTranslations($locale = 'cs'): Nette\Database\Table\Selection
	{
		$rows = $this->database->table('locations_videos_links_translations')->select('locations_videos_links.*, locations_videos_links_translations.*');
		if($locale!==null){
			$rows->where('locale', $locale);
		}

		return $rows;
	}

	/** @return Nette\Database\Table\Selection */
	public function findFiles(): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_files');
	}

	/** @return Nette\Database\Table\Selection */
	public function findFilesArs(): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_files_ars');
	}

	/** @return Nette\Database\Table\Selection */
	public function findFilesArsTranslations($locale = 'cs'): Nette\Database\Table\Selection
	{
		$rows = $this->database->table('locations_files_ars_translations')->select('locations_files_ars.*, locations_files_ars_translations.*');
		if($locale!==null){
			$rows->where('locale', $locale);
		}

		return $rows;
	}

	/** @return Nette\Database\Table\Selection */
	public function findModels(): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_models');
	}

	/** @return Nette\Database\Table\Selection */
	public function findModelsFiles(): Nette\Database\Table\Selection
	{
		return $this->database->table('locations_models_files');
	}

	/** @return Nette\Database\Table\Selection */
	public function findModelsFilesTranslations($locale = 'cs'): Nette\Database\Table\Selection
	{
		$rows = $this->database->table('locations_models_files_translations')->select('locations_models_files.*, locations_models_files_translations.*');
		if($locale!==null){
			$rows->where('locale', $locale);
		}

		return $rows;
	}
}
