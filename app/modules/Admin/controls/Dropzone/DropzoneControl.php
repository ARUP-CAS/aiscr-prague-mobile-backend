<?php

namespace App\AdminModule\Controls;

use Nette\Application\UI\Control,
    Nette\Utils\Strings,
    Nette\Utils\Random,
    Nette\Utils\Image;

class DropzoneControl extends Control
{
    /** @var */
    private $filePath = '/data/temp/';

    public function render($name, $maxFiles = 300, $acceptedFiles = 'image/*')
    {
        $template = $this->template;
        $template->name = $name;
        $template->maxFiles = $maxFiles;
        $template->acceptedFiles = $acceptedFiles;
        $template->setFile(__DIR__ . '/dropzone.latte');
        $template->render();
    }

    public function handleSaveFile( $id = null )
    {
        $httpRequest = $this->presenter->getHttpRequest();
        $files = $httpRequest->getFiles();
        $file = $files["file"];

        if($file->name!=''){ 
            $file_filename = $this->generateFilenameUpload($file->name);
            $imgUrl = WWW_DIR . $this->filePath . $file_filename;

            try {
                // image
                $image = Image::fromFile($file);
                $image->resize(2500, 2500, Image::SHRINK_ONLY);
                $image->save($imgUrl);
            } catch (\Nette\Utils\UnknownImageFileException $e) {
                //file
                $file->move($imgUrl);
            }

            echo $file_filename;
        }

        exit;
    }

    public function handleRemoveFile( $filename )
    {
        if ($filename) {
            return @unlink(WWW_DIR.'/data/temp/'. $filename);
        } else {
            return false;
        }
    }

    private function generateFilenameUpload($filename){
        $ext = explode('.', $filename);
        $ext = '.'.$ext[count($ext)-1];

        $filename = substr($filename, 0, strlen($ext)*(-1)); //odstranění koncovky z názvu souboru
        $filename = Strings::webalize($filename); //odstraněné speciálních znaků
        $filename = substr($filename, 0, 100); //zkrácení názvu souboru, aby nedošlo k překročení limitu délky názvu
        $filename = $filename.'-'.Random::generate(); //přidání random stringu
        return $filename.$ext;
    }

    /**
     * @return \WebLoader\Nette\CssLoader
     */
    public function createComponentCss()
    {

        // FileCollection v konstruktoru může dostat výchozí adresář, pak není potřeba psát absolutní cesty
        $files = new \WebLoader\FileCollection(WWW_DIR);

        // kompilátoru seznam předáme a určíme adresář, kam má kompilovat
        $compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/backend/css');
        $compiler->addFileFilter(new \WebLoader\Filter\LessFilter());
        if(!\Tracy\Debugger::isEnabled()){
            $compiler->addFilter(function ($code) {
                return \CssMin::minify($code);
            });
        }

        // nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
        return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/backend/css');
    }

    /**
     * @return \WebLoader\Nette\JavaScriptLoader
     */
    public function createComponentJs()
    {
        $files = new \WebLoader\FileCollection(WWW_DIR);

        $compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/backend/js');
        $compiler->setJoinFiles(TRUE);
        if(!\Tracy\Debugger::isEnabled()){
            $compiler->addFilter(function ($code) {
                return \JSMin\JSMin::minify($code);
            });
        }

        return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/backend/js');
    }
}