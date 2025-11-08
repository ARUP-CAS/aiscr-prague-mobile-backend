<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use App\Model,
	Nette,
	Nette\Security,
	Nette\Application\UI\Form,
	Nette\Mail\Message,
	Nette\Utils\Validators,
	Nette\Utils\Html,
	DateTime;

class UsersPresenter extends \App\Module\Admin\Presenters\BasePresenter
{
	/** @var Model\UsersRepository */
	private $users;

	private $authenticator;

	/**
	 * @var \Nette\Mail\IMailer
	 * @inject
	 */
	public $mailer;

	public function __construct(Model\UsersRepository $users, Model\Authenticator $authenticator)
	{
		$this->users = $users;
		$this->authenticator = $authenticator;
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
		if(in_array("u", $this->user->getIdentity()->getRoles())){
			$this->redirect('Default:');
		}

		$this->template->data = $this->users->findAll()->where('email <> ?', 'info@tukni.cz')->order('group ASC');
	}

	/********************* views add, edit & delete *********************/


	public function renderAdd()
	{
		if(in_array("u", $this->user->getIdentity()->getRoles())){
			$this->redirect('Default:');
		}

		//$this['userForm']['save']->caption = 'Přidat uživatele';
	}

	public function renderEdit($id = 0)
	{
		if(in_array("u", $this->user->getIdentity()->getRoles())){
			$this->redirect('Default:');
		}

		$form = $this['userForm'];
		if (!$form->isSubmitted()) {
			$user = $this->users->findAll()->get($id);
			if (!$user) {
				$this->error('Záznam nenalezen!');
			}
			$form->setDefaults($user);
		}
	}

	public function actionDelete($id = 0)
	{
		if(in_array("u", $this->user->getIdentity()->getRoles())){
			$this->redirect('Default:');
		}
		
		$user = $this->users->findAll()->get($id);
		if (!$user) {
			$this->flashMessage('Záznam nenalezen!');
		}else{
			$this->flashMessage('Záznam úspěšně smazán!');
			$this->users->findAll()->get($id)->delete();
		}
		$this->redirect('default');
	}


	/********************* component factories *********************/


	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentUserForm()
	{
		$form = new Form;

		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'div class="container"';
		$renderer->wrappers['pair']['container'] = 'div class="input"';
		$renderer->wrappers['label']['container'] = null;
		$renderer->wrappers['control']['container'] = null;
		
		//přihlašovací údaje
		$form->addGroup('Přihlašovací údaje')->setOption('container', 'fieldset');
        $form->addText('email', 'E-mail')
                ->setRequired('Vyplňte prosím email.');
        $form->addPassword('password', 'Heslo');
        $form->addPassword('passwordVerify', 'Ověření hesla')
        		->addCondition(Form::FILLED)
			    ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);
        $form->addSelect('group', 'Skupina', array(
					            'u' => 'Uživatel',
					            'a' => 'Administrátor',
					        )
		        		);
        
        // osobní údaje
        $form->addGroup('Osobní údaje')->setOption('container', 'fieldset');
        $form->addText('firstname', 'Jméno');
        $form->addText('lastname', 'Přijmení');


		//submit as element BUTTON and cancel link
		$storno = Html::el()->setHtml('<div class="input storno"><a href="'.$this->link('default').'" class="button-icon"><span class="icon transition"><i></i></span><span class="label transition">Zrušit</span></a></div>');
		$form->addButton('save', NULL)
			->setOption('description', $storno); //<----- lze standardně nastavit value
      	$pretyp = $form['save']->getControlPrototype();
      	$pretyp->setName('button'); // změna prvku na button
      	$pretyp->type = 'submit'; // nastavení typu buttonu
      	$pretyp->setClass('button-icon');
      	$pretyp->addHtml(Html::el()->setHtml('<span class="icon bg-c-2"><i></i></span><span class="label bg-c-1 bg-c-2-hover transition">Uložit</span>'));

      	$form->onSuccess[] = [$this, 'userFormSucceeded'];

		return $form;
	}


	public function userFormSucceeded($button)
	{
		$values = $button->getForm()->getValues();
		$id = (int) $this->getParameter('id');
		unset($values->save);

		if ($id) {
			// edit user
            if($values->password!=$values->passwordVerify){
                $form->addError('Hesla se neshodují.', 'error');
            }
            else if($values->password=='' && $values->passwordVerify==''){
                unset($values->password);
                unset($values->passwordVerify);
                $this->users->findAll()->get($id)->update($values);
                $this->flashMessage('Záznam byl úspěšně upraven.');
            }
            else if($values->password==$values->passwordVerify){
            	$pass = new \Nette\Security\Passwords;

                $values->password = $pass->hash($values->password);
                unset($values->passwordVerify);

                $this->users->findAll()->get($id)->update($values);
                $this->flashMessage('Záznam byl úspěšně upraven.');
            }

			$this->redirect('edit', $id);
		} else {
			//add user
			if($values->password!=$values->passwordVerify){
				$form->addError('Hesla se neshodují.', 'error');
			}else{
				$values->password = Security\Passwords::hash($values->password);
				unset($values->passwordVerify);
				$values->registration = new DateTime();

				$this->users->findAll()->insert($values);
				$this->flashMessage('Záznam byl úspěšně přidán.');
			}

			$this->redirect('default');
		}
	}

	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentPasswordForm()
	{
		$form = new Form;

		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'div class="container"';
		$renderer->wrappers['pair']['container'] = 'div class="input"';
		$renderer->wrappers['label']['container'] = null;
		$renderer->wrappers['control']['container'] = null;
		
		//přihlašovací údaje
        $form->addPassword('password', 'Heslo')
        	->setRequired('Nebylo vypněno pole %label.');
        $form->addPassword('passwordVerify', 'Ověření hesla')
        	->setRequired('Nebylo vypněno pole %label.')
        		->addCondition(Form::FILLED)
			    ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);

		//submit as element BUTTON and cancel link
		$storno = Html::el()->setHtml('<div class="input storno"><a href="'.$this->link('default').'" class="button-icon"><span class="icon transition"><i></i></span><span class="label transition">Zrušit</span></a></div>');
		$form->addButton('save', NULL)
			->setOption('description', $storno); //<----- lze standardně nastavit value
      	$pretyp = $form['save']->getControlPrototype();
      	$pretyp->setName('button'); // změna prvku na button
      	$pretyp->type = 'submit'; // nastavení typu buttonu
      	$pretyp->setClass('button-icon');
      	$pretyp->addHtml(Html::el()->setHtml('<span class="icon bg-c-2"><i></i></span><span class="label bg-c-1 bg-c-2-hover transition">Uložit</span>'));

      	$form->onSuccess[] = [$this, 'passwordFormSucceeded'];

		return $form;
	}


	public function passwordFormSucceeded($button)
	{
		$values = $button->getForm()->getValues();
		$id = (int) $this->user->getId();
		unset($values->save);

        $pass = new \Nette\Security\Passwords;

        $values->password = $pass->hash($values->password);
        unset($values->passwordVerify);

        $this->users->findAll()->get($id)->update($values);
        $this->flashMessage('Heslo bylo úspěšně změněno.');

		$this->redirect('this');
	}

}
