$(document).ready(function () {
	var log = $('.logs');
	var link = $('#url');
	var startButton = $('#download');
	var done = false;
	var sup = this;

	startButton.click(function (event) {
		sup.done = false;

		var rand = Math.random().toString(36);

		function loop() {
			$.ajax({
				method: 'POST',
				url: '/pool.php?id=' + rand
			}).done(function (res) {
				if (!sup.done) {
					log.html(res);
					log.scrollTop(log[0].scrollHeight);
					loop();
				}
			});
		}

		var value = link.val();

		log.html('Be patient... your link is downloading now!');
		loop();
		$.ajax({
			method: "POST",
			url: "/down.php",
			data: {
				url: value,
				i: rand
			}
		}).done(function (done) {
			sup.done = true;
			done = JSON.parse(done);
			if (done.error) {
				alert('Error happened...\n\n' + done.error);
			}
			else {
				log.html('<h3>Download completed!</h3>');
				log.append('<h1><a href="' + done.url + '">Click here to Download your audio</a></h1>');
			}
		});
		event.preventDefault();
		return false;
	});
});