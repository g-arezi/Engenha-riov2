// Notification functions

// Mark a notification as read and redirect to its link
function markNotificationAsRead(notificationId, link) {
    fetch(`/notifications/mark-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to the notification link
            window.location.href = link;
        }
    })
    .catch(error => {
        console.error('Erro ao marcar notificação como lida:', error);
        // Still redirect even if there's an error
        window.location.href = link;
    });
}

// Mark all notifications as read
function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erro ao marcar todas as notificações como lidas:', error);
    });
}

// Add functions to global scope
window.markNotificationAsRead = markNotificationAsRead;
window.markAllAsRead = markAllAsRead;
