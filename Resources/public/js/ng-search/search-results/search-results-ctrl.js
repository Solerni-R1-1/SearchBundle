searchApp.controller('searchResultsCtrl', ['$scope', function($scope) {
	
	$scope.hasParameter = function(param) {
		var paramString = param + "=";
		var searchArray = window.location.hash.substring(2).split("&");
		var found = false;
		for (i in searchArray) {
			search = searchArray[i];
			index = search.indexOf(paramString);
			if (index == 0 && search.length > paramString.length) {
				found = true;
				break;
			}
		}
		
		return found;
	};
	
}]);