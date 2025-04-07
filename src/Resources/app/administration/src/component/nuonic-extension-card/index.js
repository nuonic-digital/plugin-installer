import template from './nuonic-extension-card.html.twig';
import './nuonic-extension-card.scss';
import { compareVersions } from 'compare-versions';

const { Component, Mixin, Filter } = Shopware;

Component.register('nuonic-extension-card', {
	template,

	inject: ['installExtensionService'],

	mixins: [Mixin.getByName('notification')],

	emits: ['update-list'],

	props: {
		extension: {
			type: Object,
			required: true,
		},
	},

	data() {
		return {
			isLoading: false,
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

			return installed;
		},

		isUpdateAvailable() {
			if (this.isInstalled) {
				return compareVersions(this.extension.availableVersion, this.extension.plugin.version);
			}
			return false;
		},

		assetFilter() {
			return Filter.getByName('asset');
		},
	},

	methods: {
		onInstall() {
			this.isLoading = true;

			const data = {
				openSourcePluginId: this.extension.id,
			};

			this.installExtensionService
				.install(data)
				.then(() => {
					this.isLoading = false;
					this.$router.push({ name: 'sw.extension.my-extensions.listing.app', query: { term: this.extension.name } });
				})
				.catch((error) => {
					this.isLoading = false;
					this.createNotificationError({
						message: this.$tc('nuonic-plugin-installer.notification.installFailed'),
						error,
					});
				});
		},

		onUpdate() {
			this.onInstall();
		},
	},
});
