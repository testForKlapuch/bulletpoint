<?php
namespace Bulletpoint\Page;

use Nette;

class Chyba4xxPage extends Nette\Application\UI\Presenter {
    public function startup() {
        parent::startup();
        if(!$this->request->isMethod(Nette\Application\Request::FORWARD)) {
            $this->error();
        }
    }


    public function renderDefault(Nette\Application\BadRequestException $exception) {
        // load template 403.latte or 404.latte or ... 4xx.latte
        $file = __DIR__ . "/templates/Chyba/{$exception->getCode()}.latte";
        $this->template->setFile(
            is_file($file) ? $file : __DIR__ . '/templates/Chyba/4xx.latte'
        );
    }

}
