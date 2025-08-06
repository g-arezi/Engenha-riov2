<?php
/**
 * Arquivo de debug para testar problemas no reply-ticket.php
 */

// Display all PHP errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Reply Ticket Debug</h1>";

echo "<h2>Session Info</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "SESSION: " . print_r($_SESSION, true);
echo "</pre>";

echo "<h2>Form Submission Test</h2>";
?>

<form action="/reply-ticket.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="ticket_id_test" value="test_ticket_id">
    
    <div>
        <label>Message:</label>
        <textarea name="message">Test message</textarea>
    </div>
    
    <div>
        <label>Attachment:</label>
        <input type="file" name="attachment">
    </div>
    
    <div>
        <label>AJAX Mode:</label>
        <select name="ajax_mode">
            <option value="0">Regular submission</option>
            <option value="1" selected>AJAX mode</option>
        </select>
    </div>
    
    <button type="submit">Test Submit</button>
</form>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const ajaxMode = document.querySelector('[name="ajax_mode"]').value === '1';
    
    if (ajaxMode) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const ticketId = 'test_ticket_id';
        
        fetch(`/reply-ticket.php?id=${ticketId}&ajax=1`, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            document.getElementById('response-status').textContent = 
                `Status: ${response.status} ${response.statusText}`;
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                document.getElementById('response-data').textContent = JSON.stringify(data, null, 2);
            } catch (e) {
                document.getElementById('response-data').textContent = text;
            }
        })
        .catch(error => {
            document.getElementById('response-data').textContent = `Error: ${error.message}`;
        });
    }
});
</script>

<h2>Response</h2>
<div>
    <pre id="response-status">Status: -</pre>
    <pre id="response-data">Response will appear here</pre>
</div>
