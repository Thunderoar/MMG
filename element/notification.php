<?php
$unreadQuery = "SELECT COUNT(*) as unread_count FROM contact_messages WHERE read_status IS NULL OR read_status = 0";
$result = mysqli_query($con, $unreadQuery);
$row = mysqli_fetch_assoc($result);
$unreadCount = $row['unread_count'];

$messagesQuery = "SELECT message_id, fullname, email, subject, message, created_at 
                  FROM contact_messages 
                  WHERE read_status IS NULL OR read_status = 0
                  ORDER BY created_at DESC
                  LIMIT 5";
$messagesResult = mysqli_query($con, $messagesQuery);
$messages = mysqli_fetch_all($messagesResult, MYSQLI_ASSOC);
?>
<div class="notification" onclick="togglePopup()">
    <span class="icon">🔔</span>
    <span class="badge"><?= htmlspecialchars($unreadCount) ?></span>
    <div class="popup" id="popup">
        <div class="popup-header">Latest Messages</div>
        <?php foreach ($messages as $message): ?>
            <div class="popup-message">
                <a href="messageCenter.php?message_id=<?= htmlspecialchars($message['message_id']) ?>">
                    <strong><?= htmlspecialchars($message['fullname']) ?></strong><br>
                    <?= htmlspecialchars($message['email']) ?><br>
                    <em><?= htmlspecialchars($message['subject']) ?></em><br>
                    <p><?= htmlspecialchars($message['message']) ?></p>
                    <small><?= htmlspecialchars($message['created_at']) ?></small>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
