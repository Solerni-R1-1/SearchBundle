searchApp.controller('ngSearchCtrl', ['$scope', '$location', 'dataSearchFactory', function($scope, $location, dataSearchFactory) {
        var _namespace = {
            'results': null,
            'query': {
                'page': 1,
                'rpp': 5,
                'q': '',
                'fs': '',
                'ss': '',
                'afs': 'mcat,type'
            },
            'filters': {}
        };

        var _filtersBuilder = function(query, facets) {
            angular.forEach(facets, function(facet) {

                $scope.data.filters[facet.name] = (function(facet) {
                    var elmnts = {};
                    elmnts['all'] = true;
                    angular.forEach(facet.value, function(elmnt) {
                        if (query.ss.split(",").indexOf(facet.name + '__' + elmnt.value) < 0) {
                            elmnts[elmnt.value] = false;
                        } else {
                            elmnts[elmnt.value] = true;
                            elmnts['all'] = false;
                        }
                    });
                    return elmnts;
                })(facet);

            });
        };


        var _search = function(query) {
            document.getElementById('slrn-wrapper').style.display = 'block';
            //console.log(query);
            dataSearchFactory.request(query).then(function(data) {
                _namespace.results = data;
                _namespace.query = query;
                $scope.data = _namespace;

            }).then(function() {
                document.getElementById('slrn-wrapper').style.display = 'none';
            }).then(function() {
                $location.search(_namespace.query);
            }).then(function() {
                _filtersBuilder(_namespace.query, $scope.data.results.facets);
            });
        };

        var _generateSelectionQuery = function(elmnts, nameFilter) {
            selectionQueryArray = [];
            if (!elmnts['all']) {
                angular.forEach(elmnts, function(value, name) {
                    if (value) {
                        selectionQueryArray.push(nameFilter + '__' + name);
                    }
                });
            }
            return selectionQueryArray.join(',');
        };


        $scope.updateQuery = function(filters, srcEventFacetName) {
            //update query values
            $scope.data.query.ss = (function(filters) {
                var elmnts = [];
                angular.forEach(filters, function(value, key) {
                    elmnts.push(_generateSelectionQuery(value, key));
                });
                return elmnts.join(',');
            })(filters);

            $scope.data.query.se = srcEventFacetName;
            _search($scope.data.query);
        };

        $scope.search = function(query) {
            _search(query);
        };


        (function() {
            var searchObject = $location.search();
            angular.extend(_namespace.query, searchObject);
            console.log(_namespace.query);
            _search(_namespace.query);
        })();

    }]);