<?php

declare(strict_types=1);

namespace App\Model;

use Nette;


class UsersRepository
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
		return $this->database->table('users');
	}
}