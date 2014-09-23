searchApp.filter('tranfo', function() {
  return function(uri, filters) {
    return Claroline.Home.path + 'transfo/' + filters + '?img_uri=' + uri;
  };
});