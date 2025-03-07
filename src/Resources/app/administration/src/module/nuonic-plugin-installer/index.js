import './page/nuonic-plugin-installer-list';

Shopware.Module.register('nuonic-plugin-installer', {
    type: 'plugin',
    name: 'nuonic-plugin-installer.extension.title',
    title: 'nuonic-plugin-installer.extension.title',
    description: 'nuonic-plugin-installer.extension.description',
    color: '#66bbe3',
    icon: 'regular-dashboard',
    navigation: [
        {
            label: 'nuonic-plugin-installer.extension.title',
            color: '#66bbe3',
            path: 'nuonic.plugin.installer.list',
            icon: 'regular-dashboard',
            parent: 'sw-extension',
            position: 100,
        },
    ],
    routes: {
        list: {
            component: 'nuonic-plugin-installer-list',
            path: 'list',
        },
    },
});
