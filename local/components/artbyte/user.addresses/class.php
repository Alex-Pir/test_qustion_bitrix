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

    /** @var string ключ параметра Выводить только активные адреса */
    const USE_ACTIVE_ADDRESSES = "USE_ACTIVE_ADDRESSES";

    /** @var string директория хранения кеша */
    const CACHE_DIR = "/artbyte_user_addresses";

    /** @var string тэг кеша */
    const CACHE_TAG = "artbyte_user_addresses";

    /** @var string ID таблицы в шаблоне компонента */
    const GRID_ID = "user_addresses";

    /** @var string поля HL-блока */
    const ID = "ID";
    const USER_ID = "UF_USER_ID";
    const ADDRESS = "UF_USER_ADDRESS";
    const ACTIVE = "UF_ACTIVE";

    /**
     * Предварительная обработка параметров компонента
     *
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);
        $arParams[self::USE_ACTIVE_ADDRESSES] = $arParams[self::USE_ACTIVE_ADDRESSES] ?? "N";

        return $arParams;
    }

    /**
     * Выполнение компонента
     *
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        global $USER, $CACHE_MANAGER;

        $pagerParameters = $GLOBALS["NavNum"];

        if (!$USER->IsAuthorized())
        {
            return;
        }

        //получаем параметры постраничной навигации
        $gridOptions = new Bitrix\Main\Grid\Options(self::GRID_ID);
        $navParams = $gridOptions->GetNavParams();
        $nav = new Bitrix\Main\UI\PageNavigation(self::GRID_ID);
        $nav->allowAllRecords(false)
            ->setPageSize($navParams["nPageSize"])
            ->initFromUri();

        try
        {
            $this->includeModule();

            if ($this->startResultCache(false, [$USER->GetID(), $this->arParams, serialize($nav)], self::CACHE_DIR))
            {
                //регистрируем тег, для управляемого кеша
                if ($this->includedTagCache())
                {
                    $CACHE_MANAGER->RegisterTag(self::CACHE_TAG);
                }

                $hlBlock = $this->getHLTable();
                $useActive = $this->arParams[self::USE_ACTIVE_ADDRESSES] === "Y";
                $pageCount = $this->getPageCount($hlBlock, $USER->GetID(), $useActive);
                $tableData = $this->getHLData($hlBlock, $USER->GetID(), $nav->getOffset(), $nav->getLimit(), $useActive);

                $nav->setRecordCount($pageCount);
                $this->arResult["PAGE_NAVIGATION"] = $nav;

                $this->arResult["ITEMS"] = $this->prepareData($tableData);
                $this->arResult["COLUMNS"] = [
                    ['id' => self::ID, 'name' => self::ID, 'sort' => self::ID, 'default' => true],
                    ['id' => self::ADDRESS, 'name' => Loc::getMessage("UA_CLASS_ADDRESS"), 'sort' => self::ADDRESS, 'default' => true]
                ];

                $this->setResultCacheKeys([
                    "ITEMS",
                    "COLUMNS",
                    "PAGE_NAVIGATION"
                ]);
            }

            $this->includeComponentTemplate();
        }
        catch (Exception $ex)
        {
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
        $hlBlock = HighloadBlockTable::getRow(
            [
                "filter" => ["=TABLE_NAME" => self::HL_TABLE_NAME]
            ]
        );

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
    protected function getHLData(array $hlBlock, int $userId, int $offset, int $limit, bool $active = false): array
    {

        if ($userId <= 0) {
            throw new ArgumentOutOfRangeException(Loc::getMessage("UA_CLASS_USER_ID_ERROR"));
        }

        $filter = [
            "=" . self::USER_ID => $userId
        ];

        if ($active) {
            $filter[self::ACTIVE] = "Y";
        }

        $hlDataClass = (HighloadBlockTable::compileEntity($hlBlock))->getDataClass();

        $result =  $hlDataClass::getList(
            array(
                "filter" => $filter,
                "select" => [self::ID, self::ADDRESS],
                "offset" => $offset,
                "limit" => $limit
            )
        );

        return $result->fetchAll();
    }

    /**
     * Получение количества записей в таблице
     *
     * @param array $hlBlock
     * @param int $userId
     * @param bool $active
     * @return int
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
   protected function getPageCount(array $hlBlock, int $userId, bool $active = false): int {
       if ($userId <= 0) {
           throw new ArgumentOutOfRangeException(Loc::getMessage("UA_CLASS_USER_ID_ERROR"));
       }

       $filter = [
           "=" . self::USER_ID => $userId
       ];

       if ($active) {
           $filter[self::ACTIVE] = "Y";
       }

       $hlDataClass = (HighloadBlockTable::compileEntity($hlBlock))->getDataClass();

       return  $hlDataClass::getList(
           array(
               "filter" => $filter,
               "count_total" => true
           )
       )->getCount();
   }

    /**
     * Подготовка данных для таблицы
     *
     * @param array $tableData
     * @return array
     */
    protected function prepareData(array $tableData): array {

        $result = [];

        foreach ($tableData as $data) {
            $result[]["data"] = [
                self::ID => $data[self::ID],
                self::ADDRESS => $data[self::ADDRESS]
            ];
        }

        return $result;
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
        if (!Loader::includeModule(self::MODULE_ID_HL)) {
            throw new SystemException(Loc::getMessage("UA_CLASS_MODULE_NOT_INSTALLED",
                ["#MODULE_ID#" => self::MODULE_ID_HL]
            ));
        }

        return true;
    }

    /**
     * Проверка, включено ли управляемое кеширование
     *
     * @return bool
     */
    protected function includedTagCache(): bool
    {
        return defined("BX_COMP_MANAGED_CACHE") && $this->arParams["CACHE_TYPE"] === "A";
    }
}