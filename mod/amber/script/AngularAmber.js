
var AngularAmber = angular.module(
	"Amber", []
).value(
	"test", "hallo welt"
).controller(
	"character001",
	function($scope) {
		$scope.name = "Faulo";
	}
);
/*
angular.element(
	document.querySelector(".Amber")
).ready(
	function() {
		angular.bootstrap(
			document.querySelector(".Amber"),
			["Amber"]
		);
    }
);
//*/