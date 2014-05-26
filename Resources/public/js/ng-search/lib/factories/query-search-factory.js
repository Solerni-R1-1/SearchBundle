searchApp.factory('querySearchFactory', ['$q', function($q) {

        var _query = {};
        var _deferred = null;


        var _forge = function(query) {
            _deferred = $q.defer();
            console.log(query);
            ngSearchService.post(query).then(function(response) {
                console.log(response);
                angular.forEach(response.data.documents, function(document) {
                    document.templateUrl = Claroline.Home.asset +
                            'bundles/orangesearch/js/ng-search/search-results/templates/' +
                            document.type_name +
                            '.html';
                });
                console.log('data: ', response.data);
                _data = response.data;
                _deferred.resolve(_data);
            }, function() {
                console.log('error service');
                _deferred.reject('Erreur');
            });
            return _deferred.promise;
        };

        return {
            request: _request
        };
    }]);


