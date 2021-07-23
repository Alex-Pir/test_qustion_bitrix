<?php

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    "NAME" => loc::getMessage("ARTBYTE_COMPONENT_NAME"),
    "DESCRIPTION" => loc::getMessage("ARTBYTE_COMPONENT_DESCRIPTION"),
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "service",
        "CHILD" => array(
            "ID" => "artbyte",
            "NAME" => loc::getMessage("ARTBYTE_COMPONENT_PARTNER")
        )
    ),
);