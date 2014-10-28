searchApp.filter('tranfo', function() {
  return function(uri, filters) {
	  var homePath = Claroline.Home.path;
	  var result = Claroline.Home.asset;
	  if (homePath === "/app_dev.php/" || homePath === "/app.php/") {
		  result = result + homePath.substring(1);
	  }
	  result = result + 'transfo/' + filters + '?img_uri=' + uri;
    return result;
  };
});