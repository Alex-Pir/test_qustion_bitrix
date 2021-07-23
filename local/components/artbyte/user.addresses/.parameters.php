<?php
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

$arComponentParameters = [
    "PARAMETERS" => [
        "USE_ACTIVE_ADDRESSES" => [
            "NAME" => Loc::getMessage("PARAMETER_USE_ACTIVE_ADDRESSES"),
            "PARENT" => "BASE",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ],
        "CACHE_TIME" => Array("DEFAULT" => 3600000),
    ]
];