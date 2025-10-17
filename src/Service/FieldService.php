<?php declare(strict_types=1);

namespace ChannelProductDescription\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\{EntityRepository, Search\Criteria, Search\Filter\EqualsFilter};
use Shopware\Core\System\CustomField\CustomFieldTypes;

/**
 * Сервис для управления custom fields (дополнительными полями товара)
 * Создает поля для каждой комбинации канал+язык
 */
class FieldService
{
    private const SET = 'ch_desc_set';  // Имя набора полей
    private const PFX = 'ch_desc_';     // Префикс для имен полей

    public function __construct(
        private EntityRepository $setRepo,   // Репозиторий для работы с наборами полей
        private EntityRepository $chRepo,    // Репозиторий каналов продаж
        private EntityRepository $langRepo   // Репозиторий языков
    ) {}

    // Создать набор полей и сами поля для всех каналов и языков
    public function createFields(Context $c): void
    {
        $cr = new Criteria();
        $cr->addFilter(new EqualsFilter('name', self::SET));
        
        // Если набор уже существует - просто синхронизируем
        if ($this->setRepo->search($cr, $c)->first()) {
            $this->syncFields($c);
            return;
        }

        // Создаем новый набор полей и привязываем к товарам
        $this->setRepo->create([[
            'name' => self::SET,
            'config' => ['label' => ['ru-RU' => 'Описания по каналам']],
            'relations' => [['entityName' => 'product']],  // Привязка к товарам
            'customFields' => $this->genFields($c)         // Генерируем поля
        ]], $c);
    }

    // Синхронизировать поля (добавить новые, если появились каналы/языки)
    public function syncFields(Context $c): void
    {
        $cr = new Criteria();
        $cr->addFilter(new EqualsFilter('name', self::SET))->addAssociation('customFields');
        
        $set = $this->setRepo->search($cr, $c)->first();
        if (!$set) return;

        $new = $this->genFields($c);      // Генерируем актуальный список полей
        $old = $set->getCustomFields();   // Получаем существующие поля
        if (!$old) return;

        // Находим поля которых еще нет
        $names = array_map(fn($f) => $f->getName(), iterator_to_array($old));
        $add = array_filter($new, fn($f) => !in_array($f['name'], $names));

        // Добавляем новые поля
        if ($add) {
            $this->setRepo->update([['id' => $set->getId(), 'customFields' => array_values($add)]], $c);
        }
    }

    // Удалить все поля при деинсталляции плагина
    public function removeFields(Context $c): void
    {
        $cr = new Criteria();
        $cr->addFilter(new EqualsFilter('name', self::SET));
        $set = $this->setRepo->search($cr, $c)->first();
        if ($set) $this->setRepo->delete([['id' => $set->getId()]], $c);
    }

    // Сгенерировать поля для всех комбинаций каналов и языков
    private function genFields(Context $c): array
    {
        $chs = $this->chRepo->search(new Criteria(), $c);   // Все каналы
        $lngs = $this->langRepo->search(new Criteria(), $c); // Все языки
        $f = [];

        // Для каждой комбинации канал+язык создаем поле
        foreach ($chs as $ch) {
            foreach ($lngs as $l) {
                $f[] = [
                    'name' => $this->name($ch->getId(), $l->getId()),
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'componentName' => 'sw-text-editor',    // Текстовый редактор
                        'customFieldType' => 'textEditor',
                        'label' => ['ru-RU' => $ch->getName() . ' / ' . $l->getName()]
                    ]
                ];
            }
        }
        return $f;
    }

    // Создать уникальное имя поля из ID канала и языка
    // Пример: ch_desc_12345678_87654321
    public function name(string $ch, string $l): string
    {
        return self::PFX . substr(str_replace('-', '', $ch), 0, 8) . '_' . substr(str_replace('-', '', $l), 0, 8);
    }
}
