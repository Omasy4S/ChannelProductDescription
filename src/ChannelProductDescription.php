<?php declare(strict_types=1);

namespace ChannelProductDescription;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\{InstallContext, UninstallContext, ActivateContext};
use ChannelProductDescription\Service\FieldService;

/**
 * Главный класс плагина для управления описаниями по каналам
 */
class ChannelProductDescription extends Plugin
{
    // При установке создаем custom fields для всех каналов и языков
    public function install(InstallContext $ctx): void
    {
        parent::install($ctx);
        $this->container->get(FieldService::class)->createFields($ctx->getContext());
    }

    // При активации синхронизируем поля (если добавились новые каналы)
    public function activate(ActivateContext $ctx): void
    {
        parent::activate($ctx);
        $this->container->get(FieldService::class)->syncFields($ctx->getContext());
    }

    // При удалении убираем все созданные поля (если пользователь не хочет сохранить данные)
    public function uninstall(UninstallContext $ctx): void
    {
        parent::uninstall($ctx);
        if (!$ctx->keepUserData()) {
            $this->container->get(FieldService::class)->removeFields($ctx->getContext());
        }
    }
}
