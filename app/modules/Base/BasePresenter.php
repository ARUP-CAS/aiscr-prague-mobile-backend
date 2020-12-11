<?php

declare(strict_types=1);

namespace App\Module\Base\Presenters;

use Nette,
	App\Model;
use Nette\Utils\Html;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @persistent */
    public $locale;

    /** @var */
    public $localeList;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    protected function startup()
    {
        parent::startup();

        $this->localeList = ['cs', 'en', 'de'];
    }

    public function translate($key)
    {
        return $this->translator->translate($key);
    }

	protected function beforeRender()
	{
		$this->template->viewName = $this->getView();
		$this->template->root = isset($_SERVER['SCRIPT_FILENAME']) ? realpath(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) : NULL;

		$a = strrpos($this->getName(), ':');
		if ($a === FALSE) {
			$this->template->moduleName = '';
			$this->template->presenterName = $this->getName();
		} else {
			$this->template->moduleName = substr($this->getName(), 0, $a + 1);
			$this->template->presenterName = substr($this->getName(), $a + 1);
        }

        $hostName = $_SERVER['HTTP_HOST']; 
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
        $this->template->baseAbsolutePath = $protocol.$hostName;
    }

	/**
	 * @param  string|NULL
	 * @return \Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = NULL): Nette\Application\UI\ITemplate
	{
		$template = parent::createTemplate($class);

		$template->getLatte()->addFilter('imageGenerator', function ($s, $crop = false, $w = 200, $h = 200, $id = 0) {
			$imageGenerator = new \ImageGenerator();
			$imageGenerator->setUrl($s);
			$imageGenerator->setCropImage($crop);
			$imageGenerator->setWidth($w);
			$imageGenerator->setHeight($h);
            $imageGenerator->setId($id);
			$thumUrl = $imageGenerator->getUrlThumb();

	        return $thumUrl;
        });

        $template->getLatte()->addFilter('nbsp', function ($text) {
            return preg_replace('/(\s)([a-zA-z])\s/i', '$1$2&nbsp;', $text);
        });

		return $template;
	}

    /**
     * @return \WebLoader\Nette\CssLoader
     */
    public function createComponentCss()
    {

        // FileCollection v konstruktoru může dostat výchozí adresář, pak není potřeba psát absolutní cesty
        $files = new \WebLoader\FileCollection(WWW_DIR);

        // kompilátoru seznam předáme a určíme adresář, kam má kompilovat
        $compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/css');
        
        // nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
        return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/css');
    }

    /**
     * @return \WebLoader\Nette\JavaScriptLoader
     */
    public function createComponentJs()
    {
        $files = new \WebLoader\FileCollection(WWW_DIR);

        $compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/js');
        $compiler->setJoinFiles(TRUE);
        if(!\Tracy\Debugger::isEnabled()){
            $compiler->addFilter(function ($code) {
               return \JSMin\JSMin::minify($code);
            });
        }

        return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/js');
    }
}
