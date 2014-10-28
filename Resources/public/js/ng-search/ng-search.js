var searchApp = angular.module('searchApp', [
    'ngResource',
    'ngSanitize',
    'ui.keypress',
    'ui.bootstrap',
    'ui.bootstrap.tpls'
]);

searchApp.config(function($sceDelegateProvider) {
	$sceDelegateProvider.resourceUrlWhitelist([
		// Allow same origin resource loads.
		'**'
	]);
});