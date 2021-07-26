<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Result;
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

    /** @var int количество записей на странице по умолчанию */
    const DEFAULT_PAGE_SIZE = 20;

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
        global $USER, $APPLICATION, $CACHE_MANAGER;

        if (!$USER->IsAuthorized()) {
            $APPLICATION->AuthForm(Loc::getMessage("UA_CLASS_USER_IS_NOT_AUTHORIZED"));
            return;
        }

        //получаем параметры постраничной навигации
        $gridOptions = new Bitrix\Main\Grid\Options(self::GRID_ID);
        $navParams = $gridOptions->GetNavParams();
        $nav = new Bitrix\Main\UI\PageNavigation(self::GRID_ID);
        $nav->allowAllRecords(false)
            ->setPageSize($navParams["nPageSize"] ?? self::DEFAULT_PAGE_SIZE)
            ->initFromUri();

        try {
            $this->includeModule();

            if ($this->startResultCache(false, [$USER->GetID(), $this->arParams, $nav->getCurrentPage()], self::CACHE_DIR)) {

                //регистрируем тег для управляемого кеша
                if ($this->includedTagCache()) {
                    $CACHE_MANAGER->RegisterTag(self::CACHE_TAG);
                }

                $hlBlock = $this->getHLTable();

                //получаем количество записей для постраничной навигации
                $pageCount = $this->getHLData(
                    $hlBlock,
                    [
                        "filter" => $this->prepareFilter(),
                        "count_total" => true
                    ]
                )->getCount();

                //получаем записи
                $tableData = $this->getHLData(
                    $hlBlock,
                    [
                        "filter" => $this->prepareFilter(),
                        "select" => $this->prepareSelect(),
                        "offset" => $nav->getOffset(),
                        "limit" => $nav->getLimit()
                    ]
                )->fetchAll();

                $nav->setRecordCount($pageCount);
                $this->arResult["PAGE_NAVIGATION"] = $nav;

                //записывем данные для таблицы
                $this->arResult["ITEMS"] = $this->prepareData($tableData);
                $this->arResult["COLUMNS"] = $this->prepareColumns();

                $this->setResultCacheKeys([
                    "ITEMS",
                    "COLUMNS",
                    "PAGE_NAVIGATION"
                ]);

                $this->includeComponentTemplate();
            }
        } catch (Exception $ex) {
            $this->abortResultCache();
            AddMessage2Log($ex->getMessage());
        }
    }

    /**
     * Подготовка фильтра для запроса
     *
     * @return array
     */
    protected function prepareFilter(): array
    {
        global $USER;

        return [
            "=" . self::USER_ID => $USER->GetID(),
            self::ACTIVE => $this->arParams[self::USE_ACTIVE_ADDRESSES] === "Y",
        ];
    }

    /**
     * Подготовка блока 'select' для запроса
     *
     * @return string[]
     */
    protected function prepareSelect(): array
    {
        return [self::ID, self::ADDRESS];
    }

    /**
     * Подготовка колонок для таблицы
     *
     * @return array
     * @throws SystemException
     */
    protected function prepareColumns(): array
    {
        $result = [];
        $selectFields = $this->prepareSelect();

        if (empty($selectFields)) {
            throw new SystemException(Loc::getMessage("UA_CLASS_SELECT_ARRAY_IS_EMPTY"));
        }

        foreach ($selectFields as $field) {
            $result[] = [
                "id" => $field,
                "name" => Loc::getMessage("UA_CLASS_PARAMETER")[$field] ?? $field,
                "sort" => $field,
                "default" => true
            ];
        }

        return $result;
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
     * @param array $parameters - массив, содержащий набор параметров для запроса
     *                            [filter, select, limit, offset и т.д.]
     * @return Result
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    protected function getHLData(array $hlBlock, array $parameters): Result
    {
        $hlDataClass = (HighloadBlockTable::compileEntity($hlBlock))->getDataClass();
        return $hlDataClass::getList($parameters);
    }

    /**
     * Подготовка данных для таблицы
     *
     * @param array $tableData
     * @return array
     */
    protected function prepareData(array $tableData): array
    {

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
            throw new LoaderException(Loc::getMessage("UA_CLASS_MODULE_NOT_INSTALLED",
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