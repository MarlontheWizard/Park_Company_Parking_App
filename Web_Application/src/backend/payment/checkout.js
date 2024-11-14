document.addEventListener('DOMContentLoaded', function() {
    // Assuming you have a button for proceeding to payment
    const paymentButton = document.getElementById('proceedToPayment');

    paymentButton.addEventListener('click', function(event) {
        event.preventDefault();  // Prevent default form submission if it's inside a form

        const selectedSpotId = document.getElementById('parkingSpotId').value; // Get selected parking spot ID
        const selectedReservationTime = document.getElementById('reservationTime').value; // Get reservation time

        if (!selectedSpotId || !selectedReservationTime) {
            alert('Please select a parking spot and reservation time.');
            return;
        }

        // Call the backend to create the checkout session
        createCheckoutSession(selectedSpotId, selectedReservationTime);
    });
});

// Function to create the checkout session by calling the backend PHP script
function createCheckoutSession(spotId, reservationTime) {
    fetch('http://localhost:8080/web_application/src/backend/payment/create-checkout-session.php', {
        method: 'POST',
        body: JSON.stringify({
            spotId: spotId,
            reservationTime: reservationTime
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.checkoutUrl) {
            // Redirect the user to the Stripe Checkout page
            window.location.href = data.checkoutUrl;
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error during checkout session creation:', error);
        alert('Something went wrong. Please try again.');
    });
}

document.getElementById('create-checkout-button').addEventListener('click', function () {
    const reservationTime = document.getElementById('reservationTime').value;
    const parkingSpotId = document.getElementById('parkingSpotId').value;

    // Make sure the reservation time is selected
    if (!reservationTime) {
        alert("Please select a time for the reservation.");
        return;
    }

    // Send the reservation details to create the checkout session
    fetch('/web_application/src/backend/payment/create-checkout-session.php', {
        method: 'POST',
        body: JSON.stringify({
            spotId: parkingSpotId,
            reservationTime: reservationTime
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.checkoutUrl) {
            // Redirect to the Stripe checkout session
            window.location.href = data.checkoutUrl;
        } else {
            alert("Failed to create payment session.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Error processing payment.");
    });
});