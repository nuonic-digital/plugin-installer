import template from './nuonic-plugin-installer-list.html.twig';
import './nuonic-plugin-installer-list.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('nuonic-plugin-installer-list', {
	template,

	inject: [
		'repositoryFactory',
		'filterFactory'
	],
	mixins: [Mixin.getByName('listing')],

	data() {
		return {
			availablePlugins: [],
			page: 1,
			limit: 25,
			total: 0,
			term: null,
			sortBy: 'name',
			sortDirection: 'ASC',
			isLoading: false,
			filterCriteria: [],
			activeFilterNumber: 0,
			defaultFilters: [
				'name-filter',
				'description-filter',
				'manufacturer-filter',
				'license-filter',
				'last-commit-filter',
			],
			storeKey: 'grid.filter.nuonic_available_opensource_plugin',
		};
	},

	computed: {
		availablePluginsRepository() {
			return this.repositoryFactory.create('nuonic_available_opensource_plugin');
		},

		availablePluginsCriteria() {
			const criteria = new Criteria(this.page, this.limit);

			criteria.setTerm(this.term);
			criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
			criteria.addAssociation('plugin');
			criteria.addFilter(Criteria.range('lastSeenAt', { gte: new Date(Date.now() - 48 * 60 * 60 * 1000) }));

			this.filterCriteria.forEach((filter) => {
				criteria.addFilter(filter);
			});

			return criteria;
		},

		listFilterOptions() {
			return {
				'name-filter': {
					property: 'name',
					type: 'string-filter',
					label: this.$tc('nuonic-plugin-installer.list.filters.nameFilter.label'),
					placeholder: this.$tc('nuonic-plugin-installer.list.filters.nameFilter.placeholder'),
					valueProperty: 'key',
					labelProperty: 'key',
					criteriaFilterType: 'contains',
				},
				'description-filter': {
					property: 'description',
					type: 'string-filter',
					label: this.$tc('nuonic-plugin-installer.list.filters.descriptionFilter.label'),
					placeholder: this.$tc('nuonic-plugin-installer.list.filters.descriptionFilter.placeholder'),
					valueProperty: 'key',
					labelProperty: 'key',
					criteriaFilterType: 'contains',
				},
				'manufacturer-filter': {
					property: 'manufacturer',
					type: 'string-filter',
					label: this.$tc('nuonic-plugin-installer.list.filters.manufacturerFilter.label'),
					placeholder: this.$tc('nuonic-plugin-installer.list.filters.manufacturerFilter.placeholder'),
					valueProperty: 'key',
					labelProperty: 'key',
					criteriaFilterType: 'contains',
				},
				'license-filter': {
					property: 'license',
					label: this.$tc('nuonic-plugin-installer.list.filters.licenseFilter.label'),
					placeholder: this.$tc('nuonic-plugin-installer.list.filters.licenseFilter.placeholder'),
					type: 'multi-select-filter',
					options: [
						{
							label: 'MIT',
							value: 'MIT',
						},
						{
							label: 'OSL-3.0',
							value: 'OSL-3.0',
						},
						{
							label: 'Apache-2.0',
							value: 'Apache-2.0',
						},
						{
							label: 'GPL-3.0',
							value: 'GPL-3.0',
						},
						{
							label: 'GPL-3.0-or-later',
							value: 'GPL-3.0-or-later',
						},
					],
				},
				'last-commit-filter': {
					property: 'lastCommitTime',
					label: this.$tc('nuonic-plugin-installer.list.filters.lastCommitFilter.label'),
					dateType: 'date',
					fromFieldLabel: null,
					toFieldLabel: null,
					showTimeframe: true,
				}
			};
		},

		listFilters() {
			return this.filterFactory.create('nuonic_available_opensource_plugin', this.listFilterOptions);
		},

		assetFilter() {
			return Shopware.Filter.getByName('asset');
		},
	},

	methods: {
		async getList() {
			this.isLoading = true;

			let criteria = await Shopware.Service('filterService').mergeWithStoredFilters(
				this.storeKey,
				this.availablePluginsCriteria,
			);

			this.activeFilterNumber = criteria.filters.length - 1;

			try {
				const response = await this.availablePluginsRepository.search(criteria);

				this.availablePlugins = response;
				this.total = response.total;
				this.isLoading = false;
			} catch {
				this.isLoading = false;
			}
		},

		onChangeLanguage() {
			this.getList();
		},

		changePage({ page = 1, limit = 10 }) {
			this.page = page;
			this.limit = limit;
			this.getList();
		},

		onSearch(value) {
			this.term = value;
			this.getList();
		},

		updateCriteria(criteria) {
			this.page = 1;
			this.filterCriteria = criteria;
			return this.getList();
		},
	},
});
