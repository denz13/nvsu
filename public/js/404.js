$(document).ready(function() {
    // Define the expected sequence of letters in uppercase
    var expectedSequence = ['T', 'A', 'E'];
    var currentSequence = [];

    $(document).on('keydown', function(event) {
        // Get the pressed key in uppercase
        var pressedKey = String.fromCharCode(event.which).toUpperCase();

        // Check if the pressed key matches the next expected letter
        if (pressedKey === expectedSequence[currentSequence.length]) {
            currentSequence.push(pressedKey);

            // Check if the full sequence has been pressed
            if (currentSequence.length === expectedSequence.length) {

                // Load another window or redirect to another page
                window.location.href = '/admin/dashboard';  // Replace with the desired URL

                // Reset the current sequence
                currentSequence = [];


            }
        } else {
            // Reset the sequence if an incorrect key is pressed
            currentSequence = [];
            console.log('MALI');
        }
    });
});
