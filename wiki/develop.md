# Особенности разработки на портале

**Основная рабочая ветка для боевого сайта `production`**

Основные кроны:
```cronexp 
*/1 * * * * /usr/bin/php -f /home/bitrix/ext_www/tknovosib/core/bitrix/modules/main/tools/cron_events.php
*/1 * * * * /usr/bin/php -f /home/bitrix/ext_www/tknovosib/core/bitrix/php_interface/cron_events.php
0 10 * * * /usr/bin/php -f /home/bitrix/ext_www/tknovosib/core/local/tools/vacation_business/event.php
```

---

### 1. Работа со сделками
> Сотрудники работают со сделками, переводят на разные стадии и воронки и при завершении сделки или ее закрытие, сделка не должна закрываться.

Ссылка на искомый файл в gitlab: [crm_deal.php](https://git.vedteam.ru/web/tknovosib/-/blame/production/core/bitrix/modules/crm/classes/general/crm_deal.php?page=4#L3290 "Ссылка на искомый файл в gitlab")

Что бы сделка не закрывалась:
1. нужно в файле `/home/bitrix/ext_www/tknovosib/core/bitrix/modules/crm/classes/general/crm_deal.php`
2. в классе `CAllCrmDeal` метод `Update` приблизительно на строке `3293`
3. комментируем `CCrmActivity::SetAutoCompletedByOwner(CCrmOwnerType::Deal, $ID);`

Ориентировочный код.
```php
//region Complete activities if entity is closed
if($arRow['STAGE_SEMANTIC_ID'] !== $currentFields['STAGE_SEMANTIC_ID'] && $currentFields['STAGE_SEMANTIC_ID'] !== Bitrix\Crm\PhaseSemantics::PROCESS)
{
    CCrmActivity::SetAutoCompletedByOwner(CCrmOwnerType::Deal, $ID);
}
```

После каждого обновления модуля crm текущий файл обычно перезаписывается. Нужно смотреть, какие обновления установятся и проследить что бы нужная строчка кода была закомментирована.

В итоге получим такой результат.
```php
//region Complete activities if entity is closed
if($arRow['STAGE_SEMANTIC_ID'] !== $currentFields['STAGE_SEMANTIC_ID'] && $currentFields['STAGE_SEMANTIC_ID'] !== Bitrix\Crm\PhaseSemantics::PROCESS)
{
    //CCrmActivity::SetAutoCompletedByOwner(CCrmOwnerType::Deal, $ID);
}
```

> В будующем планируется реализовать без модификации ядра и реализовать штатными методами!

---

#### 2. Имя пользователя в timeline

путь к скомпилированному файлу: core/bitrix/js/crm/timeline/item/dist/index.bundle.js
Строка: 918

меняем на:
```js
 template: `<a :class="className" :href="detailUrl" target="_blank" :title="title"><div class="customTitleUser">{{title}}</div><i :style="styles"></i></a>`
```

также добавлеяем стиль в файл: core/bitrix/js/crm/timeline/item/dist/index.bundle.css
```css
.customTitleUser{
	float: right;
	width: max-content;
	margin-right: 24px;
}
```

---
