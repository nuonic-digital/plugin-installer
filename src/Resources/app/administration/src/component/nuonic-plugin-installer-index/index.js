import template from './nuonic-plugin-installer-index.html.twig';
import './nuonic-plugin-installer-index.scss';

// eslint-disable-next-line no-undef
const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('nuonic-plugin-installer-index', {
  template,

  inject: ['repositoryFactory'],

  computed: {
    async packages() {
      const pluginRepository = this.repositoryFactory.create('nuonic_available_opensource_plugin')
      return await pluginRepository.search(new Criteria)
    }
  }
});
