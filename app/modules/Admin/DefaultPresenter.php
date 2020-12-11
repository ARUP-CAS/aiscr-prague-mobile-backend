<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model;


/**
 * Default presenter.
 */
class DefaultPresenter extends \App\Module\Admin\Presenters\BasePresenter
{

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
		
	}
}