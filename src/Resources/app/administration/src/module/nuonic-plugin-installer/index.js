Shopware.Module.register('nuonic-plugin-installer', {
  type: 'plugin',
  name: 'NuonicPluginInstaller',
  title: 'nuonic-plugin-installer.extension.title',
  description: 'nuonic-plugin-installer.extension.description',
  color: '#66bbe3',
  icon: 'regular-dashboard',
  navigation: [{
    label: 'NuonicPluginInstaller',
    color: '#66bbe3',
    path: 'nuonic.plugin.installer.index',
    icon: 'regular-dashboard',
    parent: 'sw-extension',
    position: 100
  }],
  routes: {
    index: {
      component: 'nuonic-plugin-installer-index',
      path: '/'
    },
  },
});
