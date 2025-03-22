const { Application } = Shopware;

Application.addServiceProviderDecorator('searchTypeService', searchTypeService => {
    searchTypeService.upsertType('nuonic_available_opensource_plugin', {
        entityName: 'nuonic_available_opensource_plugin',
        placeholderSnippet: 'nuonic-plugin-installer.extension.listing.placeholderSearchBar',
        listingRoute: 'nuonic.plugin.installer.list',
        hideOnGlobalSearchBar: false,
    });

    return searchTypeService;
});
