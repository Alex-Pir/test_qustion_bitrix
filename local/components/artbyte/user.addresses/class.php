<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentOutOfRangeException;
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
class UserAddresses extends CBitrixComponent
{

    /** @var string ID модуля Highload-блоки */
    const MODULE_ID_HL = "highloadblock";

    /** @var string навзвание таблицы HL-блока */
    const HL_TABLE_NAME = "artbyte_hl_user_addresses";

    /** @var string поля HL-блока */
    const USER_ID = "UF_USER_ID";
    const ADDRESS = "UF_ADDRESS";
    const ACTIVE = "UF_ACTIVE";

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


            $this->initComponentTemplate();
        } catch (Exception $ex) {
            $this->abortResultCache();
            AddMessage2Log($ex->getMessage());
        }
    }

    /**
     * Получение информации о HL-блоке
     *
     * @return array
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function getHLTable(): array
    {

        $hlBlock = HighloadBlockTable::getList(
            [
                "filter" => ["=NAME" => self::HL_TABLE_NAME]
            ]
        )->fetch();

        if (!$hlBlock) {
            throw new SystemException(
                Loc::getMessage(Loc::getMessage("UA_CLASS_HL_NOT_FOUND",
                    ["#HL_NAME#" => self::HL_TABLE_NAME]))
            );
        }

        return $hlBlock;
    }

    /**
     * Получение данных из HL-блока
     *
     * @param array $hlBlock
     * @param int $userId
     * @param bool $active
     * @return array
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function getHLData(array $hlBlock, int $userId, bool $active = false): array
    {

        if ($userId <= 0)
        {
            throw new ArgumentOutOfRangeException(Loc::getMessage("UA_CLASS_USER_ID_ERROR"));
        }

        $filter = [
            self::USER_ID => $userId
        ];

        if ($active)
        {
            $filter[self::ACTIVE] = "Y";
        }

        $hlDataClass = (HighloadBlockTable::compileEntity($hlBlock))->getDataClass();

        return $hlDataClass::getList(
            array(
                "filter" => $filter,
                "select" => [self::ADDRESS]
            )
        )->fetchAll();
    }

    /**
     * Подключение модуля Highloadblock
     *
     * @return bool
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    protected function includeModule(): bool
    {
        if (!Loader::includeModule(self::MODULE_ID_HL))
        {
            throw new SystemException(Loc::getMessage("UA_CLASS_MODULE_NOT_INSTALLED",
                ["#MODULE_ID#" => self::MODULE_ID_HL]
            ));
        }

        return true;
    }
}