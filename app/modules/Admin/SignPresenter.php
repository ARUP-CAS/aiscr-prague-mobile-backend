<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use App\Model,
	Nette,
	Nette\Mail\Message,
	Nette\Application\UI;


class SignPresenter extends \App\Module\Admin\Presenters\BasePresenter
{
	/** @persistent */
	public $backlink = '';

	/** @var Model\UsersRepository */
	private $users;

	/**
	 * @var \Nette\Mail\IMailer
	 * @inject
	 */
	public $mailer;

	public function __construct(Model\UsersRepository $users)
	{
		$this->users = $users;
	}

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$form->addText('email', 'E-mail:')
			->setRequired('Vložte prosím váš email.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Vložte prosím vaše heslo.');

		$form->addSubmit('send', 'Přihlásit se');

		$form->onSuccess[] = [$this, 'signInFormSucceeded'];
		
		return $form;
	}


	public function signInFormSucceeded($form, $values)
	{
		try {
			$this->getUser()->login($values->email, $values->password, NULL);

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
			return;
		}

		$this->restoreRequest($this->backlink);
		$this->redirect('Default:');
	}

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentForgotPasswordForm()
	{
		$form = new UI\Form;
		$form->addText('email', 'E-mail:')
			->setRequired('Vložte prosím váš email.');

		$form->addSubmit('send', 'Obnovit heslo');

		$form->onSuccess[] = [$this, 'forgotPasswordFormSucceeded'];
		
		return $form;
	}


	public function forgotPasswordFormSucceeded($form, $values)
	{

		$user = $this->users->findAll()->where('email', $values->email)->fetch();
		if($user){
			$password = Nette\Utils\Random::generate(8);
			$password_hash = Nette\Security\Passwords::hash($password);

			$this->users->findAll()->wherePrimary($user->id)->update([
					'password' => $password_hash
				]);

			$mail = new Message;
			$mail->setFrom($this->getContext()->parameters['emails']['info']);
			$mail->addTo($user->email);
			$mail->setSubject('Obnovení hesla na webu '.strtoupper($this->getContext()->parameters['type']));
			$mail->setHTMLBody('
					<p>
						<strong>Vaše nové heslo:</strong> '.$password.'
					</p>
				');

			try {
				$this->mailer->send($mail);
			} catch (\Exception $e) {
				\Tracy\Debugger::log(new \Exception($e->getMessage())); // log failed send email
			}
			
			$this->flashMessage('Nové heslo vám bylo odesláno na váš e-mail');	
		}
		
		$this->redirect('in');
	}


	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl/a jsi úspěšně odhlášen.');
		$this->redirect('in');
	}

}
