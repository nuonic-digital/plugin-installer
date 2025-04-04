import template from './nuonic-plugin-installer-list.html.twig';
import './nuonic-plugin-installer-list.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('nuonic-plugin-installer-list', {
	template,

	inject: ['repositoryFactory'],
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
			loading: false,
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
			return criteria;
		},
	},

	methods: {
		getList() {
			this.loading = true;
			this.availablePluginsRepository.search(this.availablePluginsCriteria).then((result) => {
				this.availablePlugins = result;
				this.total = result.total;
				this.loading = false;
			});
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
	},
});
