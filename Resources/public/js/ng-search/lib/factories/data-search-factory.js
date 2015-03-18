searchApp.factory('dataSearchFactory', ['$q', 'ngSearchService', function($q, ngSearchService) {

        var _data = {};
        var _deferred = null;


        var _request = function(query) {
            _deferred = $q.defer();
            ngSearchService.get(query).then(function(response) {
                angular.forEach(response.data.documents, function(document) {
                    document.templateUrl = Claroline.Home.asset +
                            'bundles/orangesearch/js/ng-search/search-results/templates/' +
                            document.type_name + /*'_' + window.Claroline.Home.locale +*/
                            '.html';
                });
                
                angular.forEach(response.data.facets, function(facet) {
                    facet.templateUrl = Claroline.Home.asset +
                            'bundles/orangesearch/js/ng-search/search-filters/templates/' +
                            facet.type +
                            '.html';
                });
                
                //console.log('data: ', response.data);
                _data = response.data;
                _deferred.resolve(_data);
            }, function() {
                //console.log('error service');
                _deferred.reject('Erreur');
            });
            return _deferred.promise;
        };

        return {
            request: _request
        };
    }]);