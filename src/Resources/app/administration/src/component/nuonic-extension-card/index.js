import template from './nuonic-extension-card.html.twig';
import './nuonic-extension-card.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;
const { Utils, Filter } = Shopware;

Component.register('nuonic-extension-card', {
	template,

	compatConfig: Shopware.compatConfig,

	inheritAttrs: false,

	inject: ['shopwareExtensionService', 'extensionStoreActionService', 'cacheApiService'],

	emits: ['update-list'],

	mixins: ['sw-extension-error'],

	props: {
		extension: {
			type: Object,
			required: true,
		},
	},

	data() {
		return {
			isLoading: false,
			showUninstallModal: false,
			showRemovalModal: false,
			showPermissionsModal: false,
			permissionsAccepted: false,
			showPrivacyModal: false,
			permissionModalActionLabel: null,
			openLink: null,
			showConsentAffirmationModal: false,
			consentAffirmationDeltas: null,
		};
	},

	computed: {
		dateFilter() {
			return Shopware.Filter.getByName('date');
		},

		defaultThemeAsset() {
			return this.assetFilter('administration/static/img/theme/default_theme_preview.jpg');
		},

		imageUrl() {
			const location = window.location.origin;
			const path = '/api/_action/nuonic-plugin-installer/proxy';

			if (!this.extension.icon) {
				return this.defaultThemeAsset;
			}

			const query = new URLSearchParams({
				url: this.extension.icon,
			});

			const url = `${location}${path}?${query}`;
			return url;
		},

		isInstalled() {
			const installed = this.extension.pluginId !== null;

			console.log('isInstalled', installed);

			return installed;
		},

		assetFilter() {
			return Filter.getByName('asset');
		},
	},

	methods: {
		emitUpdateList() {
			this.$emit('update-list');
		},
	},
});
