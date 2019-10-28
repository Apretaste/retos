$(document).ready(function(){
	$('.tabs').tabs();
	$('.modal').modal();
});

function formatCredit(credit) {
	credit = Math.round(credit * 100) / 100;
	return "§" + credit;
}
