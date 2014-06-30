searchApp.filter('tranfo', function() {
  return function(uri, filters) {
    console.log(Claroline.Home.path + '/transfo/' + filters + '?uri=' + uri);
    return Claroline.Home.path + 'transfo/' + filters + '?uri=' + uri;
  };
});