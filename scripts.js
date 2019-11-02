$(document).ready(function(){
	$('.tabs').tabs();
	$('.modal').modal();
});

function formatCredit(credit) {
	credit = Math.round(credit * 100) / 100;
	return "§" + credit;
}

function pad(n, width, z) {
	z = z || '0';
	n = n + '';
	return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

// formats a date and time
function formatDateTime(dateStr) {
	var date = new Date(dateStr);
	var month = date.getMonth();
	var day = pad(date.getDay(),2);
	var hour = (date.getHours() < 12) ? date.getHours() : date.getHours() - 12;
	var minutes = date.getMinutes();
	if (minutes < 10) {
		minutes = '0' + minutes;
	}
	var amOrPm = (date.getHours() < 12) ? "am" : "pm";
	return day + '/' + pad(month,2) + '/' + date.getFullYear() + ' ' + hour + ':' + minutes + amOrPm;
}
