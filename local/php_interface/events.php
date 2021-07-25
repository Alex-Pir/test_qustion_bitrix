<?php

$eventManager = Bitrix\Main\EventManager::getInstance();

/** изменение записи в highloadblock'е  Адреса пользователей */
$eventManager->addEventHandler(
    "",
    "UserAddressesOnAfterUpdate",
    ["Artbyte\\HighloadBlock\\Handlers", "hlOnAfterEdit"]
);

/** добавление записи в highloadblock  Адреса пользователей */
$eventManager->addEventHandler(
    "",
    "UserAddressesOnAfterAdd",
    ["Artbyte\\HighloadBlock\\Handlers", "hlOnAfterEdit"]
);

/** удаление записи из highloadblock'а  Адреса пользователей */
$eventManager->addEventHandler(
    "",
    "UserAddressesOnAfterDelete",
    ["Artbyte\\HighloadBlock\\Handlers", "hlOnAfterEdit"]
);