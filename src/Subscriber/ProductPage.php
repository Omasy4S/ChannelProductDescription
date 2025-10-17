<?php declare(strict_types=1);

namespace ChannelProductDescription\Subscriber;

use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Подписчик на загрузку страницы товара в витрине
 * Заменяет стандартное описание на описание для текущего канала/языка
 */
class ProductPage implements EventSubscriberInterface
{
    // Подписываемся на событие загрузки страницы товара
    public static function getSubscribedEvents(): array
    {
        return [ProductPageLoadedEvent::class => 'load'];
    }

    // При загрузке страницы - подменяем описание если есть кастомное
    public function load(ProductPageLoadedEvent $e): void
    {
        $p = $e->getPage()->getProduct();  // Товар
        $c = $e->getSalesChannelContext(); // Контекст (канал + язык)
        
        // Формируем имя поля для текущего канала и языка
        $n = 'ch_desc_' . substr(str_replace('-', '', $c->getSalesChannel()->getId()), 0, 8) 
                        . '_' . substr(str_replace('-', '', $c->getContext()->getLanguageId()), 0, 8);
        
        $f = $p->getCustomFields();
        
        // Если есть кастомное описание и оно не пустое - используем его
        if ($f && isset($f[$n]) && trim($f[$n])) {
            $p->setDescription($f[$n]);
        }
        // Иначе остается стандартное описание (fallback)
    }
}
