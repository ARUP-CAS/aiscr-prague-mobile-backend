<?php

namespace App\Module\Admin\Controls;

use Nette\Application\UI\Control;

class LocaleControlFormControl extends Control
{
    /** @var */
    private $filePath = '/data/temp/';

    public function render($type = 'input', $frm = '', $label = '', $name = '', $table = '', $table_col = '', $table_col_id = '', $class = '')
    {
        $template = $this->template;
        $template->type = $type;
        $template->frm = $frm;
        $template->label = $label;
        $template->name = explode('___', $name);
        $template->table_name = isset($template->name[1]) ? $template->name[1] : false;
        $template->name = $template->name[0];
        $template->table = $table;
        $template->table_col = $table_col;
        $template->table_col_id = $table_col_id;
        $template->class = $class;
        $template->localeList = $this->presenter->localeList;
        $template->setFile(__DIR__ . '/LocaleControlForm.latte');
        $template->render();
    }

    public function getLocaleValue($table, $col_id, $id, $locale){
        return $this->presenter->database->table($table)->where($col_id, $id)->where('locale', $locale)->fetch();
    }
}