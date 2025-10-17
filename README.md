# Описания товаров по каналам - Shopware 6

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Shopware 6](https://img.shields.io/badge/Shopware-6.5%2B-blue)](https://www.shopware.com/)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple)](https://www.php.net/)

Плагин для Shopware 6, позволяющий управлять описаниями товаров для разных каналов продаж и языков.

## 📋 Возможности

- ✅ **Компактный код** - Всего ~270 строк кода
- ✅ **Поддержка каналов** - Разные описания для каждого канала продаж
- ✅ **Мультиязычность** - Автоматическое определение языка
- ✅ **Автосинхронизация** - Автоматическое создание полей при добавлении каналов
- ✅ **Fallback механизм** - Использует стандартное описание если кастомное пустое
- ✅ **Чистая архитектура** - Сервисы, подписчики, DI конфигурация

## 🚀 Установка

```bash
# Скопируйте плагин в директорию custom/plugins
cp -r ChannelProductDescription custom/plugins/

# Установите и активируйте
bin/console plugin:refresh
bin/console plugin:install --activate ChannelProductDescription
bin/console cache:clear
```

## 📖 Использование

1. Откройте товар в админ-панели
2. Найдите секцию "Описания по каналам"
3. Введите описание для нужного канала/языка
4. Сохраните

Если поле пустое, будет использовано стандартное описание товара.

## 🏗️ Архитектура

```
src/
├── ChannelProductDescription.php    # Главный класс плагина (37 строк)
├── Service/
│   └── FieldService.php             # Управление кастомными полями (108 строк)
├── Subscriber/
│   ├── ChannelSync.php              # Автосинхронизация при изменении каналов (28 строк)
│   └── ProductPage.php              # Логика отображения в витрине (39 строк)
└── Resources/
    ├── config/
    │   └── services.xml             # Конфигурация Dependency Injection
    └── app/administration/
        └── src/
            ├── main.js              # Точка входа админки
            └── module/sw-product/
                └── component/       # Vue.js компонент (49 строк)
```

**Всего:** ~270 строк кода (включая комментарии)

## 🔧 Технические детали

### Генерация кастомных полей

Плагин автоматически генерирует кастомные поля для каждой комбинации:
- Канал продаж (например, Storefront, Headless)
- Язык (например, Английский, Немецкий)

Формат имени поля: `ch_desc_{channelId}_{languageId}`

### Подписчики событий

1. **ChannelSync** - Слушает события `sales_channel.written` и `sales_channel.deleted`
2. **ProductPage** - Слушает `ProductPageLoadedEvent` для замены описаний

### Логика Fallback

```php
if (customDescription && !empty(customDescription)) {
    product.description = customDescription;
} else {
    // Используется стандартное описание
}
```

## 📦 Требования

- **Shopware:** 6.5+
- **PHP:** 8.1+
- **Composer:** 2.0+

## 🧪 Тестирование

```bash
# Проверка статуса плагина
bin/console plugin:list | grep ChannelProductDescription

# Проверка кастомных полей
bin/console debug:container | grep FieldService

# Проверка подписчиков событий
bin/console debug:event-dispatcher | grep ProductPage
```

## 📝 Заметки о разработке

- **Время разработки:** ~8 часов
- **Стиль кода:** PSR-12
- **Комментарии:** На русском языке
- **Архитектура:** Чистая архитектура, принципы SOLID
- **Область:** Только backend (PHP, сервисы, подписчики событий)

## 🤝 Участие в разработке

Это демонстрационный проект для портфолио. Можете форкнуть и модифицировать.

## 📄 Лицензия

MIT License - см. файл [LICENSE](LICENSE) для деталей.

## 👤 Автор

Создано как демонстрация технического задания.

## 🔗 Ссылки

- [Документация Shopware](https://developer.shopware.com/)
- [Руководство по разработке плагинов](https://developer.shopware.com/docs/guides/plugins/)

---

**Примечание:** Этот плагин был разработан как демонстрации навыков разработки плагинов для Shopware 6.
