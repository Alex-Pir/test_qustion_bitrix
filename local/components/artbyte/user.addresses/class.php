<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

/**
 * Класс компонента Адреса пользователей.
 * Компонент предназначен для получения из HL-блока
 * адресов текущего пользователя (если пользователь авторизован)
 *
 * Class UserAddresses
 */
class UserAddresses extends CBitrixComponent {

    /** @var string ID модуля Highload-блоки */
    const MODULE_ID_HL = "highloadblock";


    /**
     * Выполнение компонента
     *
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        global $USER, $CACHE_MANAGER;

        if (!$USER->IsAuthorized()) {
            return;
        }

        try {

            $this->includeModule();

        } catch (Exception $ex) {
            $this->abortResultCache();
            AddMessage2Log($ex->getMessage());
        }
    }

    /**
     * Подключение модуля Highloadblock
     *
     * @return bool
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    protected function includeModule(): bool {
        if (!Loader::includeModule(self::MODULE_ID_HL)) {
            throw new SystemException(Loc::getMessage("UA_CLASS_MODULE_NOT_INSTALLED",
                ["#MODULE_ID#" => self::MODULE_ID_HL]
            ));
        }

        return true;
    }
}