$(document).ready(function(){
	$('.tabs').tabs();
	$('.modal').modal();
});

function formatCredit(credit) {
	credit = Math.round(credit * 100) / 100;
	return "§" + credit;
}

function remove(id){
	$("#skip").modal('open');
	var removeAction = $("#remove-action");
	removeAction.prop("onclick", null).unbind("click");
	removeAction.prop("onclick", null).off("click");
	removeAction.click(function(){
		apretaste.send({command: 'RETOS QUITAR', data: {challenge: id}});
	});
}