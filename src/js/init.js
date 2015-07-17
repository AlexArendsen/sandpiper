var app = angular.module('sandpiper', [])

function showMessage(msg,timeout) {
	Materialize.toast(msg,timeout||4000)
}

$(document).ready(function(){
	// Initialize Sidenav
	$('.button-collapse').sideNav()

	// Initialize modal triggers on demand
	$('body').on("click",".modal-trigger:not(.modal-trigger-initialized)",function(){
		$('.modal-trigger:not(.modal-trigger-initialized)').leanModal().addClass('modal-trigger-initialized')
		$(this).trigger('click')
	})
})
