const ApiService = Shopware.Classes.ApiService;
const { Application } = Shopware;

class InstallExtensionService extends ApiService {
	constructor(httpClient, loginService, apiEndpoint = 'nuonic-plugin-installer') {
		super(httpClient, loginService, apiEndpoint);
	}

	install(formData) {
		const headers = this.getBasicHeaders({});

		return this.httpClient
			.post(`_action/nuonic-plugin-installer/install`, formData, {
				headers,
			})
			.then((response) => {
				return ApiService.handleResponse(response);
			});
	}
}

Application.addServiceProvider('installExtensionService', (container) => {
	const initContainer = Application.getContainer('init');
	return new InstallExtensionService(initContainer.httpClient, container.loginService);
});

export default InstallExtensionService;
