var app = angular.module('sandpiper', [])

function showMessage(msg,timeout) {
	Materialize.toast(msg,timeout||4000)
}

$(document).ready(function(){
	// Initialize Sidenav
	$('.button-collapse').sideNav()
})
