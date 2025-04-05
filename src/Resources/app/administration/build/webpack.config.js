module.exports = (params) => {
	return {
		resolve: {
			modules: [`${params.basePath}/Resources/app/administration/node_modules`],
		},
	};
};
