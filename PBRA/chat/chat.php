<?php
session_start();
include "../mypbra_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['id'])) {
  header("Location: login.php");
  exit();
}

$loggedInId = $_SESSION['id'];
$loggedInName = $_SESSION['full_name'];

// Get logged in user profile pic
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $loggedInId);
$stmt->execute();
$result = $stmt->get_result();
$currentUser = $result->fetch_assoc();

$myProfilePic = (!empty($currentUser['profile_pic']) && file_exists('../' . $currentUser['profile_pic']))
  ? '../' . htmlspecialchars($currentUser['profile_pic'])
  : '../profile/images/default-profile.jpg';

// Update last_login timestamp for current user to track online status
$currentTime = date('Y-m-d H:i:s');
$updateActivity = $conn->prepare("UPDATE users SET last_login = ? WHERE id = ?");
$updateActivity->bind_param("si", $currentTime, $loggedInId);
$updateActivity->execute();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Chat UI</title>
  <link rel="stylesheet" href="chat.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
  <div class="chat-container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="search-box">
        <input type="text" placeholder="Search" id="searchUser">
      </div>
      <ul class="chat-list" id="chatList">
        <?php

        // Get favourite contact IDs for the logged-in user
        $stmt = $conn->prepare("SELECT contact_id FROM chat_user_contacts WHERE user_id = ? AND is_favorite = 1");
        $stmt->bind_param("i", $loggedInId);
        $stmt->execute();
        $result = $stmt->get_result();

        $favouriteIds = [];
        while ($row = $result->fetch_assoc()) {
          $favouriteIds[] = $row['contact_id'];
        }
        $stmt->close();

        // Fetch all other users with their last_login except the logged-in user
        $stmt = $conn->prepare("SELECT id, full_name, profile_pic, last_login FROM users WHERE id != ?");
        $stmt->bind_param("i", $loggedInId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($user = $result->fetch_assoc()):
          $initials = implode('', array_map(function ($n) {
            return strtoupper($n[0]);
          }, explode(' ', $user['full_name'])));
          $profilePic = (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic']))
            ? '../' . htmlspecialchars($user['profile_pic'])
            : '../profile/images/default-profile.jpg';

          // Check if user is online (active in last 5 minutes)
          $isOnline = false;
          if (!empty($user['last_login'])) {
            $lastActivity = new DateTime($user['last_login']);
            $now = new DateTime();
            $diff = $now->diff($lastActivity);
            $secondsDiff = ($diff->days * 24 * 60 * 60) + ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
            $isOnline = $secondsDiff < 30; // 30 seconds threshold
          }
        ?>
          <li data-userid="<?= $user['id'] ?>" data-profile="<?= $profilePic ?>" data-status="<?= $isOnline ? 'online' : 'offline' ?>">
            <div class="avatar-container">
              <?php if (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic'])): ?>
                <img src="<?= $profilePic ?>" alt="<?= htmlspecialchars($user['full_name']) ?>" class="avatar">
              <?php else: ?>
                <div class="avatar-circle"><?= $initials ?></div>
              <?php endif; ?>
            </div>
            <div class="chat-info">
              <h4><?= htmlspecialchars($user['full_name']) ?></h4>
              <p>Say hello...</p>
            </div>
            <div class="notification-badge" id="notification-<?= $user['id'] ?>" style="display: none;">0</div>
            <div class="status-dot <?= $isOnline ? 'online' : '' ?>"></div>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>

    <!-- Chat Area -->
    <div class="chat-area">
      <div class="chat-header">
        <button class="back-btn" onclick="history.back()">
          <i class="fas fa-arrow-left"></i>
        </button>
        <div class="avatar-container">
          <div class="avatar-circle" id="chatAvatar">??</div>
        </div>
        <div>
          <h4 id="chatName">Select a User</h4>
          <span class="status offline">Offline</span>
          <!-- Favourite button -->
          <button id="favBtn" class="fav-btn">☆</button>
        </div>
      </div>

      <div class="chat-messages" id="chatMessages"></div>

      <div class="chat-input">
        <input type="file" id="fileAttachment" class="file-input" multiple>
        <button class="attachment-btn" onclick="document.getElementById('fileAttachment').click()">
          <i class="fas fa-paperclip"></i>
        </button>
        <input type="text" id="msgBox" placeholder="Send a message">
        <button onclick="sendMessage()">SEND</button>
      </div>
    </div>
  </div>

  <script>
    let currentReceiverId = null;
    let currentReceiverPic = null;
    let currentReceiverStatus = "offline";
    const myProfilePic = "<?= $myProfilePic ?>";
    let favorites = []; // Array to store favorite contact IDs

    // Load favorites when page loads
    async function loadFavorites() {
      try {
        const response = await fetch('get_favourite.php?action=list');
        const data = await response.json();
        if (data.success) {
          favorites = data.favorites;

          const chatList = document.getElementById('chatList');
          const allItems = Array.from(chatList.querySelectorAll('li'));

          // Reset all items first
          allItems.forEach(li => li.classList.remove('favorite'));

          // Loop favourites and move them to top
          favorites.forEach(favId => {
            const favItem = allItems.find(li => parseInt(li.dataset.userid) === favId);
            if (favItem) {
              favItem.classList.add('favorite');
              chatList.prepend(favItem); // Move favourite to top
            }
          });
        }
      } catch (error) {
        console.error('Error loading favorites:', error);
      }
    }


    // Call this function on page load
    loadFavorites();

    // Load chat list click events
    document.querySelectorAll('.chat-list li').forEach(item => {
      item.addEventListener('click', function() {
        document.querySelectorAll('.chat-list li').forEach(li => li.classList.remove('active'));
        this.classList.add('active');

        currentReceiverId = this.dataset.userid;
        currentReceiverPic = this.dataset.profile;
        currentReceiverStatus = this.dataset.status || "offline";
        const name = this.querySelector('h4').textContent;
        document.getElementById('chatName').textContent = name;

        // Update header avatar
        const chatAvatarContainer = document.querySelector('.chat-header .avatar-container');
        chatAvatarContainer.innerHTML = '';

        if (this.querySelector('img.avatar')) {
          // User has profile pic
          const img = document.createElement('img');
          img.src = currentReceiverPic;
          img.alt = name;
          img.className = 'avatar';
          chatAvatarContainer.appendChild(img);
        } else {
          // User has no profile pic, show initials
          const initials = name.split(' ').map(n => n[0]).join('');
          const avatarCircle = document.createElement('div');
          avatarCircle.className = 'avatar-circle';
          avatarCircle.textContent = initials;
          chatAvatarContainer.appendChild(avatarCircle);
        }

        // Update status indicator
        const statusElement = document.querySelector('.chat-header .status');
        statusElement.textContent = currentReceiverStatus;
        statusElement.className = 'status ' + currentReceiverStatus;

        // Update favorite button
        const isFavorite = favorites.includes(parseInt(currentReceiverId));
        const favBtn = document.getElementById('favBtn');
        if (favBtn) {
          favBtn.textContent = isFavorite ? '★' : '☆';
        }

        // Check favorite status for this contact
        checkFavoriteStatus(currentReceiverId);

        loadMessages();
      });
    });

    // Check if a contact is favorited
    async function checkFavoriteStatus(contactId) {
      if (!contactId) return;

      try {
        const formData = new FormData();
        formData.append('contact_id', contactId);

        const response = await fetch('get_favourite.php?action=status', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();
        if (data.success) {
          const favBtn = document.getElementById('favBtn');
          favBtn.textContent = data.is_favorite ? '★' : '☆';
        }
      } catch (error) {
        console.error('Error checking favorite status:', error);
      }
    }

    // Function to check for online users and update status
    function updateOnlineStatus() {
      fetch('get_online_users.php')
        .then(response => response.json())
        .then(data => {
          // Update status for each user in the chat list
          document.querySelectorAll('.chat-list li').forEach(li => {
            const userId = li.dataset.userid;
            const statusDot = li.querySelector('.status-dot');

            if (data.online_users.includes(parseInt(userId))) {
              li.dataset.status = "Online";
              if (statusDot) statusDot.classList.add('online');

              // Update current chat header if this is the active chat
              if (userId === currentReceiverId) {
                const statusElement = document.querySelector('.chat-header .status');
                statusElement.textContent = "Online";
                statusElement.className = 'status online';
              }
            } else {
              li.dataset.status = "Offline";
              if (statusDot) statusDot.classList.remove('online');

              // Update current chat header if this is the active chat
              if (userId === currentReceiverId) {
                const statusElement = document.querySelector('.chat-header .status');
                statusElement.textContent = "Offline";
                statusElement.className = 'status offline';
              }
            }
          });
        })
        .catch(error => console.error('Error fetching online status:', error));
    }

    // Update online status initially and then every 5 seconds
    updateOnlineStatus();
    setInterval(updateOnlineStatus, 5000);

    // Load chat messages
    async function loadMessages() {
      if (!currentReceiverId) return;

      // Hide notification badge when chat is opened
      const badgeElement = document.getElementById(`notification-${currentReceiverId}`);
      if (badgeElement) {
        badgeElement.style.display = 'none';
      }

      const response = await fetch(`get_chat.php?action=load&receiver_id=${currentReceiverId}`);
      const data = await response.json();

      const chatMessages = document.getElementById('chatMessages');
      chatMessages.innerHTML = "";

      data.forEach(msg => {
        const avatarContent = msg.sender === 'me' ?
          `<img src="${myProfilePic}" alt="Me" class="avatar">` :
          (msg.profile_pic ?
            `<img src="${msg.profile_pic}" alt="Them" class="avatar">` :
            `<img src="../profile/images/default-profile.jpg" alt="Default" class="avatar">`);

        // Handle attachments
        let attachmentsHtml = "";
        if (msg.attachments && msg.attachments.length > 0) {
          attachmentsHtml = msg.attachments.map(file => renderAttachment(file)).join("");
        }

        const newMessage = `
      <div class="message-group ${msg.sender === 'me' ? 'sent' : 'received'}">
        ${msg.sender !== 'me' ? `<div class="avatar-container">${avatarContent}</div>` : ""}
        <div class="message-content">
          ${msg.message ? `<div class="message">${msg.message}</div>` : ""}
          ${attachmentsHtml}
          <div class="message-time">${msg.time}</div>
        </div>
        ${msg.sender === 'me' ? `<div class="avatar-container">${avatarContent}</div>` : ""}
      </div>`;
        chatMessages.innerHTML += newMessage;
      });

      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Send message
    async function sendMessage() {
      const msgBox = document.getElementById('msgBox');
      const fileInput = document.getElementById('fileAttachment');
      const message = msgBox.value.trim();

      if (!message && fileInput.files.length === 0) return;
      if (!currentReceiverId) return;

      const formData = new FormData();
      formData.append("message", message);
      formData.append("receiver_id", currentReceiverId);

      // append all selected files
      for (let i = 0; i < fileInput.files.length; i++) {
        formData.append("attachments[]", fileInput.files[i]);
      }

      const response = await fetch("get_chat.php?action=send", {
        method: "POST",
        body: formData
      });

      const result = await response.json();
      if (result.success) {
        msgBox.value = "";
        fileInput.value = ""; // reset attachment
        loadMessages();
      }
    }


    // Enter key sends message
    document.getElementById('msgBox').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') sendMessage();
    });

    // Search filter
    const searchInput = document.getElementById('searchUser');
    const chatItems = document.querySelectorAll('.chat-list li');

    searchInput.addEventListener('input', function() {
      const filter = this.value.toLowerCase();

      let anyVisible = false;

      chatItems.forEach(li => {
        const name = li.querySelector('h4').textContent.toLowerCase();
        if (filter !== "" && name.includes(filter)) {
          li.style.display = "flex"; // show matching contact
          anyVisible = true;
        } else {
          li.style.display = "none"; // hide non-matching
        }
      });

      // Optional: show "No results"
      if (!anyVisible && filter !== "") {
        if (!document.getElementById("noResults")) {
          const noResults = document.createElement("li");
          noResults.id = "noResults";
          noResults.textContent = "No results found";
          noResults.style.textAlign = "center";
          noResults.style.color = "#aaa";
          document.getElementById("chatList").appendChild(noResults);
        }
      } else {
        const noResults = document.getElementById("noResults");
        if (noResults) noResults.remove();
      }
    });

    // Render attachment in WhatsApp style
    function renderAttachment(file) {
      // Clean up filename by removing timestamp prefix (e.g., 1758505276_Command.docx → Command.docx)
      let displayName = file.name;
      if (displayName.match(/^\d+_/)) {
        displayName = displayName.replace(/^\d+_/, '');
      }

      // If image → show preview in chat
      if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(file.ext)) {
        return `<div class="message image-message">
                  <img src="../${file.path}" alt="${displayName}">
                </div>`;
      }

      // Map file extensions to appropriate icons and MIME types
      const fileTypes = {
        'pdf': {
          icon: '<i class="fas fa-file-pdf" style="color: #e74c3c;"></i>',
          mime: 'application/pdf'
        },
        'doc': {
          icon: '<i class="fas fa-file-word" style="color: #2980b9;"></i>',
          mime: 'application/msword'
        },
        'docx': {
          icon: '<i class="fas fa-file-word" style="color: #2980b9;"></i>',
          mime: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        },
        'xls': {
          icon: '<i class="fas fa-file-excel" style="color: #27ae60;"></i>',
          mime: 'application/vnd.ms-excel'
        },
        'xlsx': {
          icon: '<i class="fas fa-file-excel" style="color: #27ae60;"></i>',
          mime: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        },
        'ppt': {
          icon: '<i class="fas fa-file-powerpoint" style="color: #e67e22;"></i>',
          mime: 'application/vnd.ms-powerpoint'
        },
        'pptx': {
          icon: '<i class="fas fa-file-powerpoint" style="color: #e67e22;"></i>',
          mime: 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        },
        'txt': {
          icon: '<i class="fas fa-file-alt" style="color: #7f8c8d;"></i>',
          mime: 'text/plain'
        },
        'zip': {
          icon: '<i class="fas fa-file-archive" style="color: #8e44ad;"></i>',
          mime: 'application/zip'
        },
        'rar': {
          icon: '<i class="fas fa-file-archive" style="color: #8e44ad;"></i>',
          mime: 'application/x-rar-compressed'
        },
        'mp3': {
          icon: '<i class="fas fa-file-audio" style="color: #f39c12;"></i>',
          mime: 'audio/mpeg'
        },
        'mp4': {
          icon: '<i class="fas fa-file-video" style="color: #3498db;"></i>',
          mime: 'video/mp4'
        }
      };

      const fileInfo = fileTypes[file.ext] || {
        icon: '<i class="fas fa-file" style="color: #95a5a6;"></i>',
        mime: 'application/octet-stream'
      };

      return `
      <div class="whatsapp-file">
        <div class="file-header">
          <div class="file-icon">${fileInfo.icon}</div>
          <div class="file-details">
            <div class="file-name">${displayName}</div>
            <div class="file-meta">${file.ext.toUpperCase()} · ${file.size}</div>
          </div>
        </div>
        <div class="file-actions">
          <a href="../${file.path}" download="${displayName}" class="btn save full-width">Save as...</a>
        </div>
      </div>`;
    }

    // Toggle favorite status
    document.getElementById('favBtn').addEventListener('click', async function() {
      if (!currentReceiverId) return;

      try {
        const formData = new FormData();
        formData.append('contact_id', currentReceiverId);

        const response = await fetch('get_favourite.php?action=toggle', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();
        if (data.success) {
          this.textContent = data.is_favorite ? '★' : '☆';

          const userId = parseInt(currentReceiverId);
          const contactElement = document.querySelector(`.chat-list li[data-userid="${userId}"]`);

          if (data.is_favorite) {
            // Add to favorites
            if (!favorites.includes(userId)) {
              favorites.push(userId);
            }
            contactElement.classList.add('favorite');
          } else {
            // Remove from favorites
            favorites = favorites.filter(id => id !== userId);
            contactElement.classList.remove('favorite');
          }
        }
      } catch (error) {
        console.error('Error toggling favorite:', error);
      }
    });

    // Add function to check for unread messages
    async function checkUnreadMessages() {
      try {
        const response = await fetch('get_chat.php?action=unread');
        const data = await response.json();

        if (data.success) {
          const unreadCounts = data.unread;
          const chatList = document.getElementById('chatList');
          const allItems = Array.from(chatList.querySelectorAll('li'));

          // Reset unread class
          allItems.forEach(li => li.classList.remove('unread'));

          allItems.forEach(li => {
            const userId = parseInt(li.dataset.userid);
            const badgeElement = document.getElementById(`notification-${userId}`);

            if (unreadCounts[userId] && unreadCounts[userId] > 0) {
              badgeElement.textContent = unreadCounts[userId];
              badgeElement.style.display = 'flex';
              li.classList.add('unread'); // Mark as unread
            } else {
              badgeElement.style.display = 'none';
            }
          });

          // Reorder list: Unread → Favorites → Others
          const unreadItems = allItems.filter(li => li.classList.contains('unread'));
          const favoriteItems = allItems.filter(li => li.classList.contains('favorite') && !li.classList.contains('unread'));
          const otherItems = allItems.filter(li => !li.classList.contains('unread') && !li.classList.contains('favorite'));

          chatList.innerHTML = '';
          unreadItems.forEach(item => chatList.appendChild(item));
          favoriteItems.forEach(item => chatList.appendChild(item));
          otherItems.forEach(item => chatList.appendChild(item));
        }
      } catch (error) {
        console.error('Error checking unread messages:', error);
      }
    }


    // Modify the existing loadMessages function to hide notification when chat is opened
    async function loadMessages() {
      if (!currentReceiverId) return;

      // Hide notification badge when chat is opened
      const badgeElement = document.getElementById(`notification-${currentReceiverId}`);
      if (badgeElement) {
        badgeElement.style.display = 'none';
      }

      const response = await fetch(`get_chat.php?action=load&receiver_id=${currentReceiverId}`);
      const data = await response.json();

      const chatMessages = document.getElementById('chatMessages');
      chatMessages.innerHTML = "";

      data.forEach(msg => {
        const avatarContent = msg.sender === 'me' ?
          `<img src="${myProfilePic}" alt="Me" class="avatar">` :
          (msg.profile_pic ?
            `<img src="${msg.profile_pic}" alt="Them" class="avatar">` :
            `<img src="../profile/images/default-profile.jpg" alt="Default" class="avatar">`);

        // Handle attachments
        let attachmentsHtml = "";
        if (msg.attachments && msg.attachments.length > 0) {
          attachmentsHtml = msg.attachments.map(file => renderAttachment(file)).join("");
        }

        const newMessage = `
      <div class="message-group ${msg.sender === 'me' ? 'sent' : 'received'}">
        ${msg.sender !== 'me' ? `<div class="avatar-container">${avatarContent}</div>` : ""}
        <div class="message-content">
          ${msg.message ? `<div class="message">${msg.message}</div>` : ""}
          ${attachmentsHtml}
          <div class="message-time">${msg.time}</div>
        </div>
        ${msg.sender === 'me' ? `<div class="avatar-container">${avatarContent}</div>` : ""}
      </div>`;
        chatMessages.innerHTML += newMessage;
      });

      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Add unread message check to the page load
    checkUnreadMessages();

    // Set interval to check for unread messages periodically
    setInterval(checkUnreadMessages, 5000);

    // Show all chat items by default (instead of hiding them)
    function showAllChatItems() {
      document.querySelectorAll('.chat-list li').forEach(li => {
        li.style.display = "flex"; // Show all contacts by default
      });
    }

    // Call this when page loads
    showAllChatItems();

    // Auto refresh messages every 5 seconds
    setInterval(loadMessages, 5000);
  </script>
</body>

</html>
