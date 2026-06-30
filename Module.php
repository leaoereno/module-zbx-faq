<?php declare(strict_types = 1);

namespace Modules\ZbxFaq;

use Zabbix\Core\CModule,
    APP,
    CMenuItem,
    CMenu,
    CWebUser;

class Module extends CModule {

    public function init(): void {
        if (CWebUser::getType() < USER_TYPE_ZABBIX_USER) {
            return;
        }

        try {
            $menu = APP::Component()->get('menu.main');
        }
        catch (\Exception $e) {
            return;
        }

        $userType = CWebUser::getType();

        $submenu = (new CMenu())
            ->add((new CMenuItem(_('Artigos')))->setAction('zbx.faq.articles'))
            ->add((new CMenuItem(_('Escrever Artigo')))->setAction('zbx.faq.write'));

        if ($userType >= USER_TYPE_ZABBIX_ADMIN) {
            $submenu->add((new CMenuItem(_('Revisão')))->setAction('zbx.faq.review'));
        }

        if ($userType >= USER_TYPE_SUPER_ADMIN) {
            $submenu->add((new CMenuItem(_('Administração')))->setAction('zbx.faq.admin'));
        }

        $menu->add(
            (new CMenuItem(_('FAQ')))->setIcon('zi-copy')
                ->setSubMenu($submenu)
        );
    }
}
