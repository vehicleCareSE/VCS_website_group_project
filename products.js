// Open Popup
function openPopup(popupId) {
    document.getElementById(popupId).style.display = 'flex';
}

// Close Popup
function closePopup(popupId) {
    document.getElementById(popupId).style.display = 'none';
}

// Show Product Details
function showProductDetails(product) {
    document.getElementById('details-image').src = product.image;
    document.getElementById('details-name').textContent = product.name;
    document.getElementById('details-description').textContent = product.description;
    document.getElementById('details-price').textContent = product.price;
    document.getElementById('details-stock').textContent = product.stock;
    openPopup('product-details-popup');
}

// Attach click event to the Add Product button
document.getElementById('add-product-btn').addEventListener('click', () => {
    openPopup('add-product-popup');
});
