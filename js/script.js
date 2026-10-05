let navbar = document.querySelector('.header .flex .navbar');
let profile = document.querySelector('.header .flex .profile');

document.querySelector('#menu-btn').onclick = () => {
    navbar.classList.toggle('active');
    profile.classList.remove('active');
}

document.querySelector('#user-btn').onclick = () => {
    profile.classList.toggle('active');
    navbar.classList.remove('active');
}

window.onscroll = () => {
    profile.classList.remove('active');
    navbar.classList.remove('active');
}

function confirmAction(id, actionType, buttonElement = null) {
    let actionUrl = '';
    let messageText = '';

    const confirmActionButton = document.getElementById('confirmActionButton');
    const confirmActionLink = document.getElementById('confirmActionLink');
    const modalMessageElement = document.getElementById('modalMessage');
    const deleteModalElement = document.getElementById('deleteModal');

    if (!confirmActionButton || !confirmActionLink || !modalMessageElement || !deleteModalElement) {
        console.error("Modal elements not found for confirmAction.");
        return;
    }

    confirmActionButton.onclick = null;
    confirmActionButton.style.display = 'none';
    confirmActionLink.href = '#';
    confirmActionLink.style.display = 'none';


    if (actionType === 'delete-cart') {
        actionUrl = `cart.php?delete=${id}`;
        messageText = 'Are you sure you want to delete this item from your cart?';
        confirmActionLink.href = actionUrl;
        confirmActionLink.style.display = 'inline-block';
    } else if (actionType === 'delete-wishlist') {
        actionUrl = `wishlist.php?delete=${id}`;
        messageText = 'Are you sure you want to delete this item from your wishlist?';
        confirmActionLink.href = actionUrl;
        confirmActionLink.style.display = 'inline-block';
    } else if (actionType === 'delete-order') {
        actionUrl = `orders.php?delete=${id}`;
        messageText = 'Are you sure you want to delete this specific order?';
        confirmActionLink.href = actionUrl;
        confirmActionLink.style.display = 'inline-block';
    } else if (actionType === 'print-order') {
        messageText = 'Are you sure you want to print this order?';
        confirmActionButton.style.display = 'inline-block';
        confirmActionButton.onclick = function () {
            closeModal();
            printOrderLogic(buttonElement, id);
        };
    } else {
        console.error("Unknown action type:", actionType);
        return;
    }

    modalMessageElement.textContent = messageText;
    deleteModalElement.style.display = 'flex';
}

function printOrderLogic(buttonElement, orderId) {
    if (!buttonElement) {
        console.error("Button element not provided for printOrderLogic.");
        return;
    }

    var box = buttonElement.closest('.box');
    if (box) {
        var printContentClone = box.cloneNode(true);

        var buttonsToHide = printContentClone.querySelectorAll('.btn, .delete-btn');
        buttonsToHide.forEach(function (button) {
            button.remove();
        });

        var printWindow = window.open('', '_blank', 'height=600,width=800');
        printWindow.document.write('<!DOCTYPE html><html><head><title>Grocery List ' + orderId + '</title>');

        var baseUrl = window.location.protocol + "//" + window.location.host;
        printWindow.document.write('<link rel="stylesheet" href="' + baseUrl + '/css/style.css" type="text/css" />');
        printWindow.document.write('</head><body>');

        printWindow.document.write('<section class="placed-orders"><div class="box-container">');
        printWindow.document.write(printContentClone.outerHTML);
        printWindow.document.write('</div></section>');

        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
}

function closeModal() {
    const deleteModalElement = document.getElementById('deleteModal');
    if (deleteModalElement) {
        deleteModalElement.style.display = 'none';
        const confirmActionButton = document.getElementById('confirmActionButton');
        if (confirmActionButton) {
            confirmActionButton.onclick = null;
        }
        const confirmActionLink = document.getElementById('confirmActionLink');
        if (confirmActionLink) {
            confirmActionLink.href = '#';
        }
    } else {
        console.error("Element with ID 'deleteModal' not found for closing.");
    }
}

window.onclick = function (event) {
    let modal = document.getElementById('deleteModal');
    if (modal && event.target === modal) {
        closeModal();
    }
}