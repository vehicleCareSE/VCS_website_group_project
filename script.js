// Initialize card positions
let originalOrder = [];

// On page load, store the initial order of cards
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.card');
    originalOrder = Array.from(cards);
});

// Toggle Favorite Icon and Move to Appropriate Position
function toggleFavorite(event, icon) {
    event.stopPropagation(); // Prevent triggering the card click
    const card = icon.closest('.card');
    const container = document.getElementById('cards-container');

    icon.classList.toggle('active'); // Toggle active class

    if (icon.classList.contains('active')) {
        // Move card to the first position
        container.prepend(card);
    } else {
        // Move card back to its original position
        restoreOriginalPosition(container, card);
    }
}

// Restore Card to Its Original Position
function restoreOriginalPosition(container, card) {
    const currentOrder = Array.from(container.children);
    const originalIndex = originalOrder.indexOf(card);

    // If the card is not already in the correct position
    if (originalIndex !== -1 && currentOrder[originalIndex] !== card) {
        // Remove the card and reinsert it at the original position
        container.removeChild(card);
        if (originalIndex >= container.children.length) {
            container.appendChild(card);
        } else {
            container.insertBefore(card, container.children[originalIndex]);
        }
    }
}

// Navigate to Page
function navigateToPage(url) {
    window.location.href = url;
}


// Function to confirm logout
function confirmLogout(event) {
    event.preventDefault(); // Prevent the default link action

    // Show confirmation dialog
    const userConfirmed = confirm("Are you sure, \nyou want to logout?");

    if (userConfirmed) {
        // Redirect to the logout page if user selects "Yes"
        window.location.href = event.target.href;
    }
    // Do nothing if the user selects "No"
}