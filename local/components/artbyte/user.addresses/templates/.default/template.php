<?php
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

use Bitrix\Main\UI\PageNavigation;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

if (empty($arResult)) {
    return;
}

$APPLICATION->includeComponent(
    "bitrix:main.ui.grid",
    "",
    [
        "GRID_ID" => "user_addresses",
        "ROWS" => $arResult["ITEMS"],
        "COLUMNS" => $arResult["COLUMNS"],
        "NAV_OBJECT" => $arResult["PAGE_NAVIGATION"]
    ],
    $this->getComponent()
);