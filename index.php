<?php
?>
<html>
<head>
    <title>Download youtube/soundcloud links as audio files.</title>
    <link href='https://fonts.googleapis.com/css?family=Quicksand' rel='stylesheet' type='text/css'>
    <style>
        body {
            background: #000000;
            text-align: center;
            font-family: Quicksand;
            color: #ffffff;
            padding-top: 10vh;
        }

        a {
            color: #ffffff;
        }

        body img.header {
            width: 100%;
            max-width: 627px;
        }

        .logs {
            height: 250px;
            overflow-y: hidden;
        }

        .url-input {
            min-width: 200px;
            max-width: 500px;
            width: 50%;
            height: 40px;
            font-size: 20px;
            text-align: center;
            border-radius: 10px;
        }

        .download-button {
            border: 1px solid white;
        }


    </style>
    <script src="//code.jquery.com/jquery-2.2.3.min.js"></script>
    <script>
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
                        url: '/youtube/pool.php?id=' + rand
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
                    url: "/youtube/down.php",
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
                        log.append('<a href="' + done.url + '">Click here to Download your audio</a>');
                    }
                });
                event.preventDefault();
                return false;
            });
        });
    </script>
</head>
<body>
    <img class="header" src="./dl.png"/>
    <h1>Download Videos as Audio.</h1>
    <p>Currently works for <a href="http://www.youtube.com">Youtube</a>,<a href="http://www.soundcloud.com">Soundcloud</a></p>
    <div class="logs"></div>
    <br>
    <br>
    <br>
    <input class="url-input" type="text" id="url" placeholder="Enter the desired URL here..."><br>
    <br>
    <a href="#" class="download-button" id="download">Begin Download</a>
</body>
</html>
<?php
?>
