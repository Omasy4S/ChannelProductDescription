import template from './sw-product-channel-descriptions.html.twig';

// Регистрируем компонент для управления описаниями по каналам
Shopware.Component.register('sw-product-channel-descriptions', {
    template,
    inject: ['repositoryFactory'],
    
    // Данные компонента: списки каналов, языков и флаг загрузки
    data: () => ({ salesChannels: [], languages: [], isLoading: false }),
    
    computed: {
        // Получаем товар из родительского компонента
        product() { return this.$parent.$parent.product; },
        
        // Генерируем все комбинации канал+язык
        combinations() {
            const c = [];
            this.salesChannels.forEach(ch => this.languages.forEach(l => 
                c.push({ channel: ch, language: l, fieldName: this.name(ch.id, l.id) })
            ));
            return c;
        }
    },
    
    // При создании компонента загружаем каналы и языки
    created() { this.load(); },
    
    methods: {
        // Загрузить все каналы и языки из API
        async load() {
            const cr = new Shopware.Data.Criteria();
            this.salesChannels = await this.repositoryFactory.create('sales_channel').search(cr, Shopware.Context.api);
            this.languages = await this.repositoryFactory.create('language').search(cr, Shopware.Context.api);
        },
        
        // Сформировать имя поля (должно совпадать с PHP)
        name(ch, l) { return `ch_desc_${ch.replace(/-/g, '').substring(0, 8)}_${l.replace(/-/g, '').substring(0, 8)}`; },
        
        // Получить значение поля
        getFieldValue(n) { return this.product.customFields?.[n] || ''; },
        
        // Обновить значение поля
        updateFieldValue(n, v) {
            if (!this.product.customFields) this.$set(this.product, 'customFields', {});
            this.$set(this.product.customFields, n, v);
        }
    }
});
