var app = angular.module('sandpiper', [])

function showMessage(msg,timeout) {
	Materialize.toast(msg,timeout||4000)
}

// Initialize Sidenav
$(document).ready(function(){
	$('.button-collapse').sideNav()
})