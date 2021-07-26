<?php
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

// заменяем $arResult эпилога значением, сохраненным в шаблоне
if(isset($arResult["arResult"])) {
    $arResult =& $arResult["arResult"];
} else {
    return;
}

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
    $this->__component
);