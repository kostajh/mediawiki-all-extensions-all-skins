( function () {

    // Use setInterval() to call function after every 1 sec
    var x = setInterval(
        function () {

                var countDownDate = new Date(mw.config.get('endTime')).getTime();

                // Get current date and time
                var currentTime = new Date().getTime();

                // Find the distance between now and the count down date
                var remainingTime = countDownDate - currentTime;

                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
                var hours = Math.floor(( remainingTime % (1000 * 60 * 60 * 24) ) / (1000 * 60 * 60));
                var minutes = Math.floor(( remainingTime % (1000 * 60 * 60) ) / (1000 * 60));
                var seconds = Math.floor(( remainingTime % (1000 * 60) ) / 1000);

                // Output the result in an element with id="countDownClock"
                $('#countDownClock').text(
                    mw.message( 'countDownClock-days', days ).text() + ' ' + mw.message( 'countDownClock-hours', hours ).text() + ' ' +
                    mw.message( 'countDownClock-minutes', minutes ).text() + ' ' + mw.message( 'countDownClock-seconds', seconds ).text() 
                );

            // If the count down is over, write expired msg
            if (remainingTime < 0 ) {
                clearInterval(x);
                $('#countDownClock').text(mw.message('countDownClock-expired'));
            }
        }, 1000
    );

}() );