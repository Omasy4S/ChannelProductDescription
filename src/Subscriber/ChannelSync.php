<?php declare(strict_types=1);

namespace ChannelProductDescription\Subscriber;

use ChannelProductDescription\Service\FieldService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Подписчик на события каналов продаж
 * Автоматически синхронизирует поля при добавлении/удалении каналов
 */
class ChannelSync implements EventSubscriberInterface
{
    public function __construct(private FieldService $svc) {}

    // Подписываемся на события создания и удаления каналов
    public static function getSubscribedEvents(): array
    {
        return ['sales_channel.written' => 'sync', 'sales_channel.deleted' => 'sync'];
    }

    // При изменении каналов - синхронизируем поля
    public function sync($e): void
    {
        $this->svc->syncFields($e->getContext());
    }
}
