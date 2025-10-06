<?php
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Virtual Meeting</title>
  <link rel="stylesheet" href="virtualmeeting.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .compatibility-error {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
      background-color: #333;
      color: white;
      padding: 20px;
      text-align: center;
      border-radius: 8px;
    }

    .compatibility-error p {
      margin: 0;
      font-size: 16px;
    }
  </style>
</head>

<body onload="fetchNotifications()">
  <?php include __DIR__ . '/../navbar/navbar.php';

  $is_admin = false;
  if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_type);
    if ($stmt->fetch() && ($user_type === 'admin' || $user_type === 'super_admin')) {
      $is_admin = true;
    }
    $stmt->close();
  }
  ?>

  <div class="page-title">
    <h1 style="font-size: 30px;">Virtual Meeting</h1>
  </div>

  <div class="virtual-carousel">
    <div class="virtual-header">
      <div class="title">
        <h1 style="font-size: 25px;">My Meeting</h1>
      </div>
      <div style="display: flex; justify-content: space-between; flex-grow: 1; margin-left: 20px;">
        <button class="add-virtual-toggle" style="margin-left: auto; margin-right: 26px;" onclick="window.location.href='meeting_history.php'">History
        </button>
        <?php if ($is_admin): ?>
          <button class="add-virtual-toggle" onclick="openModal()">+ Create Meeting</button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div id="meetingsContainer"></div>

  <!-- Create Meeting Modal -->
  <div id="meetingModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('meetingModal')">&times;</span>

      <!-- Calendar -->
      <div class="calendar">
        <div class="calendar-header">
          <button class="nav-btn" onclick="changeMonth(-1)">&#8592;</button>
          <h3 id="monthYear"></h3>
          <button class="nav-btn" onclick="changeMonth(1)">&#8594;</button>
        </div>
        <div class="calendar-grid" id="calendarGrid"></div>
      </div>

      <!-- Event Form -->
      <div class="form-container">
        <h2>Create Meeting</h2>
        <form id="meetingForm">
          <label>Invite Participants:</label>
          <div class="search-wrapper" style="position: relative;">
            <input type="text" id="searchInput" placeholder="Search participants...">
            <div class="search-results" id="searchResults"></div>
          </div>
          <h3>Invited Participants:</h3>
          <ul id="invitedList"></ul>

          <label>Date:</label>
          <input type="text" id="eventDate" readonly>

          <label>Start Time:</label>
          <input type="time" id="startTime">

          <label>End Time:</label>
          <input type="time" id="endTime">

          <label>Title:</label>
          <input type="text" id="title">

          <label>Agenda:</label>
          <textarea id="agenda"></textarea>

          <div class="form-buttons">
            <button type="submit" class="btn-save">Save</button>
            <button type="button" class="btn-cancel" onclick="closeModal('meetingModal')">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <!-- Join Meeting Modal -->
  <div id="joinMeetingModal" class="modal-meeting">
    <div class="modal-content-meeting">
      <!-- Participants area -->
      <div class="participants">
        <div class="active-speaker participant-tile">
          <!-- Your own camera -->
          <video id="localVideo" autoplay playsinline muted></video>

          <!-- Other participant -->
          <video id="remoteVideo" autoplay playsinline></video>
        </div>
        <div id="remoteContainer-join"></div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar">
        <button class="ctrl" onclick="toggleMic()">
          <i class="fas fa-microphone"></i>
          <span>Audio</span>
        </button>
        <button class="ctrl" onclick="toggleCamera()">
          <i class="fas fa-video"></i>
          <span>Video</span>
        </button>
        <button class="ctrl" onclick="openChat(currentMeetingId)">
          <i class="fas fa-comment"></i>
          <span>Chat</span>
        </button>
        <button class="ctrl" onclick="startScreenShare()">
          <i class="fas fa-desktop"></i>
          <span>Share</span>
        </button>
        <button id="recordBtn2" class="ctrl" onclick="toggleRecording()">
          <i class="fas fa-circle"></i>
          <span>Record</span>
        </button>
        <button class="ctrl end" onclick="closeModal('joinMeetingModal')">
          <i class="fas fa-sign-out-alt"></i>
          <span>Leave</span>
        </button>
      </div>

      <!-- Chat Panel -->
      <div id="chatPanel" class="chat-panel">
        <div id="chatBox" class="chat-box"></div>
        <input id="chatInput" type="text" placeholder="Type a message...">
        <button onclick="sendChat(currentMeetingId)">Send</button>
      </div>
    </div>
  </div>

  <!-- Meeting Modal For Testing -->
  <div id="testMeetingModal" class="modal">
    <div class="modal-content-meeting">
      <!-- Participants area -->
      <div class="participants">
        <div class="active-speaker participant-tile">
          <video autoplay playsinline></video>
          <div class="label" id="localUserLabel">Admin (You)</div>
        </div>
        <div id="remoteContainer-test"></div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar">
        <button class="ctrl" onclick="toggleMic()">
          <i class="fas fa-microphone"></i>
          <span>Audio</span>
        </button>
        <button class="ctrl" onclick="toggleCamera()">
          <i class="fas fa-video"></i>
          <span>Video</span>
        </button>
        <button class="ctrl" onclick="openChat('test')">
          <i class="fas fa-comment"></i>
          <span>Chat</span>
        </button>
        <button class="ctrl" onclick="shareScreen()">
          <i class="fas fa-desktop"></i>
          <span>Share</span>
        </button>
        <button id="recordBtn" class="ctrl" onclick="toggleRecording()">
          <i class="fas fa-circle"></i>
          <span>Record</span>
        </button>
        <button class="ctrl end" onclick="closeModal('testMeetingModal')">
          <i class="fas fa-sign-out-alt"></i>
          <span>End</span>
        </button>
      </div>

      <!-- Chat Panel -->
      <div id="chatPanel-test" class="chat-panel">
        <div id="chatBox-test" class="chat-box"></div>
        <input id="chatInput-test" type="text" placeholder="Type a message...">
        <button onclick="sendChat('test')">Send</button>
      </div>
    </div>
  </div>


  <!-- Edit Meeting Modal -->
  <div id="editMeetingModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span onclick="closeEditMeetingModal()" class="close">&times;</span>

      <!-- Event Form -->
      <div class="form-container">
        <h2>Edit Meeting</h2>
        <form id="editMeetingForm">
          <label>Invite Participants:</label>
          <div class="search-wrapper" style="position: relative;">
            <input type="text" id="editSearchInput" placeholder="Search participants...">
            <div class="search-results" id="editSearchResults"></div>
          </div>
          <h3>Invited Participants:</h3>
          <ul id="editInvitedList"></ul>

          <label>Date:</label>
          <div style="display: flex; align-items: center;">
            <input type="text" id="editDate" readonly style="flex-grow: 1; margin-right: 10px;">
            <button type="button" class="btn-date" onclick="openEditDatePicker()">Change Date</button>
          </div>

          <label>Start Time:</label>
          <input type="time" id="editStartTime">

          <label>End Time:</label>
          <input type="time" id="editEndTime">

          <label>Title:</label>
          <input type="text" id="editTitle">

          <label>Agenda:</label>
          <textarea id="editAgenda"></textarea>

          <div class="form-buttons">
            <button type="button" class="btn-save" onclick="updateMeeting()">Save Changes</button>
            <button type="button" class="btn-cancel" onclick="closeEditMeetingModal()">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Date Picker Modal -->
  <div id="editDatePickerModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
      <span class="close" onclick="closeEditDatePicker()">&times;</span>

      <!-- Calendar -->
      <div class="calendar">
        <div class="calendar-header">
          <button class="nav-btn" onclick="changeEditMonth(-1)">&#8592;</button>
          <h3 id="editMonthYear"></h3>
          <button class="nav-btn" onclick="changeEditMonth(1)">&#8594;</button>
        </div>
        <div class="calendar-grid" id="editCalendarGrid"></div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation -->
  <div id="deleteConfirmBox" class="popup-overlay" style="display: none;">
    <div class="popup-content">
      <h3>Are you sure you want to delete?</h3>
      <div class="popup-actions">
        <button id="deleteYes" class="btn-yes">Yes</button>
        <button id="deleteNo" class="btn-no">No</button>
      </div>
    </div>
  </div>

  <!-- Microphone Notification -->
  <div id="toast"></div>

  <!-- Save Confirmation -->
  <div id="confirmPopup" class="popup-overlay" style="display:none;">
    <div class="popup-content">
      <h3>Are you sure you want to save this meeting?</h3>
      <div class="popup-actions">
        <button id="confirmYes" class="btn-yes">Yes</button>
        <button id="confirmNo" class="btn-no">No</button>
      </div>
    </div>
  </div>

  <!-- Success Notification -->
  <div id="successPopup" class="popup-overlay" style="display: none;">
    <div class="popup-content">
      <h3 id="successMessage">Meeting has been successfully deleted!</h3>
      <div class="popup-actions">
        <button id="successOk" class="btn-yes">OK</button>
      </div>
    </div>
  </div>

  <div id="saveConfirmBox" class="popup-overlay" style="display:none;">
    <div class="popup-content">
      <h3 id="saveMessage">Meeting created successfully!</h3>
      <div class="popup-actions">
        <button id="saveOk" class="btn-yes">OK</button>
      </div>
    </div>
  </div>

  <script>
    function showPopup(message) {
      document.getElementById("successMessage").innerText = message;
      document.getElementById("successPopup").style.display = "flex";
    }
  </script>

  <!-- Recording Indicator -->
  <div id="recordingIndicator">● Recording...</div>

  <script>
    // Calendar
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();

    // Add these helper functions for media access
    function showCompatibilityMessage() {
      showPopup("Your browser doesn't support camera/microphone access. Please use a modern browser like Chrome, Firefox, or Safari.");

      // Display a more visible message in the video container
      if (currentModal === "join") {
        const localVideo = document.getElementById("localVideo");
        if (localVideo) {
          localVideo.style.display = "none";
          const parent = localVideo.parentElement;
          const msgDiv = document.createElement("div");
          msgDiv.className = "compatibility-error";
          msgDiv.innerHTML = "<p>Camera/microphone access not supported on your device or browser.</p>";
          parent.appendChild(msgDiv);
        }
      }
    }

    function showFriendlyErrorMessage(err) {
      let message = "Could not access camera/microphone. ";

      if (err.name === "NotAllowedError" || err.name === "PermissionDeniedError") {
        message += "Please grant permission to use your camera and microphone.";
      } else if (err.name === "NotFoundError") {
        message += "No camera or microphone found on this device.";
      } else if (err.name === "NotReadableError" || err.name === "AbortError") {
        message += "Your camera or microphone might be in use by another application.";
      } else {
        message += err.message || "Unknown error occurred.";
      }

      alert(message);
      console.error("Media access error:", err);
    }

    // Debug logging function
    function logDebug(message) {
      console.log(`[WebRTC ${new Date().toISOString()}] ${message}`);
    }

    // Add the initialization function
    async function initializeMedia() {
      try {
        // Check if mediaDevices is supported
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
          // Fallback for older browsers or provide alternative UI
          showCompatibilityMessage();
          return null;
        }

        logDebug("Requesting media access");
        return await navigator.mediaDevices.getUserMedia({
          video: true,
          audio: true
        });
      } catch (err) {
        console.error("Media error:", err);
        showFriendlyErrorMessage(err);
        return null;
      }
    }

    function renderCalendar(month, year) {
      const monthYear = document.getElementById("monthYear");
      const grid = document.getElementById("calendarGrid");
      grid.innerHTML = "";
      monthYear.innerText = new Date(year, month).toLocaleString("default", {
        month: "long",
        year: "numeric"
      });

      const firstDay = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();

      for (let i = 0; i < firstDay; i++) grid.innerHTML += `<div></div>`;

      for (let d = 1; d <= daysInMonth; d++) {
        let dayElem = document.createElement("div");
        dayElem.classList.add("day");
        dayElem.textContent = d;

        let today = new Date();
        let thisDate = new Date(year, month, d);

        if (thisDate < new Date(today.getFullYear(), today.getMonth(), today.getDate())) {
          dayElem.classList.add("disabled");
        } else {
          dayElem.onclick = () => {
            document.getElementById("eventDate").value = `${String(month+1).padStart(2,"0")}/${String(d).padStart(2,"0")}/${year}`;
            document.querySelectorAll(".day").forEach(el => el.classList.remove("selected"));
            dayElem.classList.add("selected");
          };
        }
        grid.appendChild(dayElem);
      }
    }

    function changeMonth(delta) {
      currentMonth += delta;
      if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
      }
      if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
      }
      renderCalendar(currentMonth, currentYear);
    }

    renderCalendar(currentMonth, currentYear);

    // Participants Search
    let invitedParticipants = [];

    document.getElementById("searchInput").addEventListener("input", function() {
      let query = this.value;
      let resultsDiv = document.getElementById("searchResults");
      if (query.length > 0) {
        fetch("search_participants.php?q=" + encodeURIComponent(query))
          .then(res => res.json())
          .then(data => {
            resultsDiv.innerHTML = "";
            if (data.length > 0) {
              data.forEach(item => {
                let div = document.createElement("div");
                div.classList.add("search-item");
                div.textContent = item.name + " (" + item.email + ")";
                div.onclick = () => {
                  addToInvited(item);
                  resultsDiv.innerHTML = "";
                  resultsDiv.style.display = "none";
                  document.getElementById("searchInput").value = "";
                };
                resultsDiv.appendChild(div);
              });
              resultsDiv.style.display = "block";
            } else resultsDiv.style.display = "none";
          });
      } else {
        resultsDiv.innerHTML = "";
        resultsDiv.style.display = "none";
      }
    });

    document.getElementById("editSearchInput").addEventListener("input", function() {
      let query = this.value;
      let resultsDiv = document.getElementById("editSearchResults");
      if (query.length > 0) {
        fetch("search_participants.php?q=" + encodeURIComponent(query))
          .then(res => res.json())
          .then(data => {
            resultsDiv.innerHTML = "";
            if (data.length > 0) {
              data.forEach(item => {
                let div = document.createElement("div");
                div.classList.add("search-item");
                div.textContent = item.name + " (" + item.email + ")";
                div.onclick = () => {
                  addToEditInvited(item);
                  resultsDiv.innerHTML = "";
                  resultsDiv.style.display = "none";
                  document.getElementById("editSearchInput").value = "";
                };
                resultsDiv.appendChild(div);
              });
              resultsDiv.style.display = "block";
            } else resultsDiv.style.display = "none";
          });
      } else {
        resultsDiv.innerHTML = "";
        resultsDiv.style.display = "none";
      }
    });

    function addToInvited(item) {
      let invitedList = document.getElementById("invitedList");
      if (document.getElementById("invited-" + item.user_id)) {
        showPopup(item.name + " is already invited.");
        return;
      }

      let li = document.createElement("li");
      li.id = "invited-" + item.user_id;
      li.textContent = item.name + " (" + item.email + ") ";
      let removeBtn = document.createElement("button");
      removeBtn.textContent = "Remove";
      removeBtn.style.marginLeft = "10px";
      removeBtn.style.cursor = "pointer";
      removeBtn.onclick = () => li.remove();
      li.appendChild(removeBtn);
      invitedList.appendChild(li);
    }

    function addToEditInvited(item) {
      if (invitedParticipants.some(p => p.id === item.user_id)) {
        showPopup(item.name + " is already invited.");
        return;
      }

      invitedParticipants.push({
        id: item.user_id,
        name: item.name,
        email: item.email
      });

      renderInvitedParticipants("editInvitedList");
    }

    function renderInvitedParticipants(containerId) {
      const container = document.getElementById(containerId);
      container.innerHTML = "";
      invitedParticipants.forEach((p, index) => {
        let li = document.createElement("li");
        // Handle missing email gracefully
        const emailPart = p.email ? ` (${p.email})` : '';
        li.textContent = `${p.name}${emailPart}`;

        // Add remove button for edit form
        if (containerId === "editInvitedList") {
          let removeBtn = document.createElement("button");
          removeBtn.textContent = "Remove";
          removeBtn.style.marginLeft = "10px";
          removeBtn.style.cursor = "pointer";
          removeBtn.onclick = () => {
            invitedParticipants.splice(index, 1);
            renderInvitedParticipants(containerId);
          };
          li.appendChild(removeBtn);
        }

        container.appendChild(li);
      });
    }

    // Create Meeting 
    document.getElementById("meetingForm").addEventListener("submit", function(e) {
      e.preventDefault();
      document.getElementById("confirmPopup").style.display = "flex";
    });

    document.getElementById("confirmYes").onclick = function() {
      document.getElementById("confirmPopup").style.display = "none";

      let invited = [];
      document.querySelectorAll("#invitedList li").forEach(li => invited.push(li.id.replace("invited-", "")));

      let rawDate = document.getElementById("eventDate").value;
      let parts = rawDate.split("/");
      let formattedDate = `${parts[2]}-${parts[0]}-${parts[1]}`;

      let data = {
        title: document.getElementById("title").value,
        agenda: document.getElementById("agenda").value,
        meeting_date: formattedDate,
        start_time: document.getElementById("startTime").value,
        end_time: document.getElementById("endTime").value,
        invited: invited
      };

      fetch("save_meeting.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
          document.getElementById("saveMessage").innerText = response.success ? "Meeting created successfully!" : "Error: " + response.message;
          document.getElementById("saveConfirmBox").style.display = "flex";
          if (response.success) {
            closeModal();
            document.getElementById("meetingForm").reset();
            document.getElementById("invitedList").innerHTML = "";
            loadMeetings();
          }
        }).catch(err => console.error(err));
    };

    document.getElementById("saveOk").onclick = function() {
      document.getElementById("saveConfirmBox").style.display = "none";
    };

    // Edit Meeting 
    let currentMeetingId = null;

    function openEditMeeting(meeting) {
      currentMeetingId = meeting.meeting_id;
      document.getElementById("editTitle").value = meeting.title;
      document.getElementById("editAgenda").value = meeting.agenda;
      document.getElementById("editDate").value = meeting.meeting_date;
      document.getElementById("editStartTime").value = meeting.start_time;
      document.getElementById("editEndTime").value = meeting.end_time;

      invitedParticipants = [...meeting.participants];
      renderInvitedParticipants("editInvitedList");

      document.getElementById("editMeetingModal").style.display = "block";
    }

    function updateMeeting() {
      const updatedData = {
        meeting_id: currentMeetingId,
        title: document.getElementById("editTitle").value,
        agenda: document.getElementById("editAgenda").value,
        date: document.getElementById("editDate").value,
        start_time: document.getElementById("editStartTime").value,
        end_time: document.getElementById("editEndTime").value,
        participants: invitedParticipants.map(p => p.id)
      };

      fetch("update_meeting.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(updatedData)
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showPopup("Meeting updated successfully!");
            closeEditMeetingModal();
            loadMeetings();
          } else {
            showPopup("Update failed: " + data.message);
          }
        });
    }

    function closeEditMeetingModal() {
      document.getElementById("editMeetingModal").style.display = "none";
    }

    // Edit Date Picker
    let editCurrentMonth = new Date().getMonth();
    let editCurrentYear = new Date().getFullYear();

    function openEditDatePicker() {
      document.getElementById("editDatePickerModal").style.display = "block";
      renderEditCalendar(editCurrentMonth, editCurrentYear);
    }

    function closeEditDatePicker() {
      document.getElementById("editDatePickerModal").style.display = "none";
    }

    function renderEditCalendar(month, year) {
      const monthYear = document.getElementById("editMonthYear");
      const grid = document.getElementById("editCalendarGrid");
      grid.innerHTML = "";
      monthYear.innerText = new Date(year, month).toLocaleString("default", {
        month: "long",
        year: "numeric"
      });

      const firstDay = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();

      for (let i = 0; i < firstDay; i++) grid.innerHTML += `<div></div>`;

      for (let d = 1; d <= daysInMonth; d++) {
        let dayElem = document.createElement("div");
        dayElem.classList.add("day");
        dayElem.textContent = d;

        dayElem.onclick = () => {
          document.getElementById("editDate").value = `${year}-${String(month+1).padStart(2,"0")}-${String(d).padStart(2,"0")}`;
          document.querySelectorAll("#editCalendarGrid .day").forEach(el => el.classList.remove("selected"));
          dayElem.classList.add("selected");
        };
        grid.appendChild(dayElem);
      }
    }

    function changeEditMonth(delta) {
      editCurrentMonth += delta;
      if (editCurrentMonth < 0) {
        editCurrentMonth = 11;
        editCurrentYear--;
      }
      if (editCurrentMonth > 11) {
        editCurrentMonth = 0;
        editCurrentYear++;
      }
      renderEditCalendar(editCurrentMonth, editCurrentYear);
    }

    const currentUserId = <?php echo $_SESSION['id']; ?>;
    const currentUsername = "<?php echo htmlspecialchars($_SESSION['full_name']); ?>";

    // Load Meetings 
    function loadMeetings() {
      fetch("get_meetings.php")
        .then(res => res.json())
        .then(response => {
          if (!response.success) {
            alert("Error loading meetings: " + response.message);
            return;
          }
          let container = document.getElementById("meetingsContainer");
          container.innerHTML = "";

          // Filter meetings to only show those where user is creator or participant
          const filteredMeetings = response.data.filter(meeting => {
            // Check if user is creator
            if (parseInt(meeting.created_by_id) === parseInt(currentUserId)) {
              return true;
            }
            // Check if user is participant
            return meeting.participants.some(p => parseInt(p.id) === parseInt(currentUserId));
          });

          // Display message if no meetings are available
          if (filteredMeetings.length === 0) {
            container.innerHTML = `
              <div class="no-meetings">
                <p>You don't have any upcoming meetings. Check back later or ask to be invited to meetings.</p>
              </div>
            `;
            return;
          }

          filteredMeetings.forEach(meeting => {
            let participants = meeting.participants.map(p => p.name).join(", ");
            let dateObj = new Date(meeting.meeting_date);
            let formattedDate = dateObj.toLocaleDateString("en-GB", {
              day: "2-digit",
              month: "long",
              year: "numeric"
            });

            function formatTime(time) {
              let [h, m] = time.split(":");
              h = parseInt(h);
              let ampm = h >= 12 ? "PM" : "AM";
              h = h % 12 || 12;
              return h + ":" + m + " " + ampm;
            }
            let formattedStart = formatTime(meeting.start_time),
              formattedEnd = formatTime(meeting.end_time);

            function calculateDuration(start, end) {
              let [sh, sm] = start.split(":").map(Number);
              let [eh, em] = end.split(":").map(Number);
              let diff = (eh * 60 + em) - (sh * 60 + sm);
              if (diff < 0) diff += 1440;
              return Math.floor(diff / 60) + " hr " + (diff % 60) + " min";
            }
            let estimatedTime = calculateDuration(meeting.start_time, meeting.end_time);

            // Check if user is the creator (for edit/delete permissions)
            const isCreator = parseInt(meeting.created_by_id) === parseInt(currentUserId);

            container.innerHTML += `
            <div class="meeting-card">
              <h2 class="meeting-title">${meeting.title}</h2>
              <h4 class="meeting-agenda">${meeting.agenda}</h4>
              <table class="meeting-info">
                <tr><td><strong>Date:</strong></td><td>${formattedDate}</td></tr>
                <tr><td><strong>Start Time:</strong></td><td>${formattedStart}</td></tr>
                <tr><td><strong>End Time:</strong></td><td>${formattedEnd}</td></tr>
                <tr><td><strong>Estimated Time:</strong></td><td>${estimatedTime}</td></tr>
                <tr><td><strong>Participants:</strong></td><td>${participants}</td></tr>
                <tr><td><strong>Created By:</strong></td><td>${meeting.created_by}</td></tr>
              </table>
              <div class="meeting-actions">
              <?php if ($is_admin): ?>
                <button class="btn delete" data-id ="${meeting.meeting_id}">Delete</button>
                <button class="btn edit" onclick='openEditMeeting(${JSON.stringify(meeting)})'>Edit</button>
              <?php else: ?>
                ${isCreator ? `<button class="btn delete" data-id="${meeting.meeting_id}">Delete</button>
                <button class="btn edit" onclick='openEditMeeting(${JSON.stringify(meeting)})'>Edit</button>` : ''}
              <?php endif; ?>
                <button class="btn test" onclick="openTestMeeting()">Test</button>
                <button class="btn join" onclick='openJoinMeeting(${JSON.stringify(meeting)})'>Join Meeting</button>
              </div>
            </div>`;
          });
        }).catch(err => console.error(err));
    }

    loadMeetings();

    // Delete Meeting
    let meetingToDelete = null,
      cardToDelete = null;
    document.addEventListener("click", function(e) {
      if (e.target.classList.contains("delete")) {
        meetingToDelete = e.target.getAttribute("data-id");
        cardToDelete = e.target.closest(".meeting-card");
        document.getElementById("deleteConfirmBox").style.display = "flex";
      }
    });

    document.getElementById("deleteYes").onclick = function() {
      document.getElementById("deleteConfirmBox").style.display = "none";
      if (!meetingToDelete) return;
      fetch("function_meeting.php?action=delete&id=" + meetingToDelete, {
          method: "GET"
        })
        .then(res => res.text())
        .then(data => {
          document.getElementById("successMessage").innerText = data;
          document.getElementById("successPopup").style.display = "flex";
          if (cardToDelete) cardToDelete.remove();
          meetingToDelete = null;
          cardToDelete = null;
        })
        .catch(err => console.error("Error:", err));
    };

    document.getElementById("deleteNo").onclick = function() {
      document.getElementById("deleteConfirmBox").style.display = "none";
      meetingToDelete = null;
      cardToDelete = null;
    };
    document.getElementById("successOk").onclick = function() {
      document.getElementById("successPopup").style.display = "none";
    };

    // Add Remote Participant Video
    function addRemoteParticipant(userId, userName, stream) {
      const container = currentModal === "test" ? document.getElementById("remoteContainer-test") : document.getElementById("remoteContainer-join");
      if (document.getElementById("participant-" + userId)) return;

      const div = document.createElement("div");
      div.classList.add("participant-tile");
      div.id = "participant-" + userId;

      const video = document.createElement("video");
      video.autoplay = true;
      video.playsInline = true;
      if (stream) video.srcObject = stream;

      const label = document.createElement("div");
      label.classList.add("label");
      label.textContent = userName;

      div.appendChild(video);
      div.appendChild(label);
      container.appendChild(div);
    }

    // Function to join the meeting
    async function openJoinMeeting(meeting) {
      currentModal = "join";
      currentMeetingId = meeting.meeting_id;
      const userId = parseInt(currentUserId);
      const creatorId = parseInt(meeting.created_by_id);

      // Check if user is invited
      if (userId !== creatorId && !meeting.participants.some(p => parseInt(p.id) === userId)) {
        showPopup("You are not invited to this meeting.");
        return;
      }

      document.getElementById("joinMeetingModal").style.display = "block";

      // Use initializeMedia instead of direct getUserMedia
      localStream = await initializeMedia();
      if (!localStream) {
        // Function will have already shown error message
        return;
      }

      // Turn off both video and audio initially
      localStream.getVideoTracks()[0].enabled = false;
      localStream.getAudioTracks()[0].enabled = false;

      // Display local video
      const localVideo = document.getElementById("localVideo");
      localVideo.srcObject = localStream;

      // Start polling for signals
      startSignalPolling(currentMeetingId, userId);

      // Notify others that we've joined
      sendSignal(currentMeetingId, userId, "join", null, null);
    }

    // Open modal for testing
    async function openTestMeeting(meeting) {
      currentModal = "test";
      currentMeetingId = meeting?.meeting_id || null;
      document.getElementById("testMeetingModal").style.display = "block";

      // Use initializeMedia instead of direct getUserMedia
      localStream = await initializeMedia();
      if (!localStream) {
        // Function will have already shown error message
        return;
      }

      // Turn off both video and audio initially
      localStream.getVideoTracks()[0].enabled = false;
      localStream.getAudioTracks()[0].enabled = false;

      const myVideo = document.querySelector("#testMeetingModal .active-speaker video");
      myVideo.srcObject = localStream;

      // Update label
      document.getElementById("localUserLabel").textContent = currentUsername + " (You)";

      // If this is a real meeting (not just testing), set up connections
      if (meeting && meeting.meeting_id) {
        // Start polling for signals
        startSignalPolling(meeting.meeting_id, currentUserId);
        // Notify others
        sendSignal(meeting.meeting_id, currentUserId, "join", null, null);
      }
    }

    // Handle signaling - send
    function sendSignal(meetingId, senderId, type, sdp, candidate, receiverId = 0) {
      const data = new FormData();
      data.append('meeting_id', meetingId);
      data.append('sender_id', senderId);
      data.append('type', type);
      data.append('receiver_id', receiverId);

      if (sdp) data.append('sdp', JSON.stringify(sdp));
      if (candidate) data.append('candidate', JSON.stringify(candidate));

      logDebug(`Sending signal: ${type} to ${receiverId || 'all'}`);

      fetch("send_signal.php", {
          method: "POST",
          body: data
        })
        .then(response => response.json())
        .then(result => {
          if (!result.success) {
            logDebug(`Signal send error: ${result.error || 'unknown'}`);
          }
        })
        .catch(err => logDebug(`Signal send failed: ${err.message}`));
    }

    // Start polling for signals
    function startSignalPolling(meetingId, userId) {
      // Clear any existing polling
      if (pollingInterval) clearInterval(pollingInterval);

      logDebug(`Starting signal polling for meeting ${meetingId}, user ${userId}`);

      // Set up polling
      pollingInterval = setInterval(() => {
        fetch(`gets_signal.php?meeting_id=${meetingId}&user_id=${userId}`)
          .then(res => res.json())
          .then(signals => {
            if (signals && signals.length) {
              logDebug(`Received ${signals.length} signals`);
              signals.forEach(processSignal);
            }
          })
          .catch(err => logDebug(`Error polling signals: ${err.message}`));
      }, 1000);
    }

    // Process incoming signals
    async function processSignal(signal) {
      const senderId = parseInt(signal.sender_id);
      logDebug(`Processing signal: ${signal.type} from user ${senderId}`);

      try {
        if (signal.type === "join") {
          // Someone joined, create peer and send offer
          logDebug(`User ${senderId} joined, creating peer connection`);
          const pc = createPeer(senderId);
          const offer = await pc.createOffer();
          await pc.setLocalDescription(offer);
          sendSignal(currentMeetingId, currentUserId, "offer", pc.localDescription, null, senderId);
        } else if (signal.type === "offer") {
          // Received offer, create answer
          logDebug(`Received offer from user ${senderId}`);
          const pc = createPeer(senderId);
          const offer = JSON.parse(signal.sdp);
          await pc.setRemoteDescription(new RTCSessionDescription(offer));
          const answer = await pc.createAnswer();
          await pc.setLocalDescription(answer);
          sendSignal(currentMeetingId, currentUserId, "answer", pc.localDescription, null, senderId);
        } else if (signal.type === "answer") {
          // Received answer to our offer
          logDebug(`Received answer from user ${senderId}`);
          const pc = peers[senderId];
          if (pc) {
            const answer = JSON.parse(signal.sdp);
            await pc.setRemoteDescription(new RTCSessionDescription(answer));
          } else {
            logDebug(`No peer connection found for user ${senderId}`);
          }
        } else if (signal.type === "candidate") {
          // Received ICE candidate
          const pc = peers[senderId];
          if (pc) {
            const candidate = JSON.parse(signal.candidate);
            await pc.addIceCandidate(new RTCIceCandidate(candidate));
            logDebug(`Added ICE candidate from user ${senderId}`);
          } else {
            logDebug(`Cannot add ICE candidate, no peer connection for user ${senderId}`);
          }
        }
      } catch (err) {
        logDebug(`Error processing signal: ${err.message}`);
      }
    }

    // Create Peer Connection
    function createPeer(remoteUserId) {
      if (peers[remoteUserId]) {
        logDebug(`Reusing existing peer connection for user ${remoteUserId}`);
        return peers[remoteUserId];
      }

      logDebug(`Creating new peer connection for user ${remoteUserId}`);
      const pc = new RTCPeerConnection(config);

      // Add local tracks if we have them
      if (localStream) {
        localStream.getTracks().forEach(track => {
          logDebug(`Adding ${track.kind} track to peer connection`);
          pc.addTrack(track, localStream);
        });
      } else {
        logDebug('No local stream to add to peer connection');
      }

      // Remote track
      pc.ontrack = (event) => {
        logDebug(`Received ${event.track.kind} track from user ${remoteUserId}`);

        if (currentModal === "join") {
          const remoteVideo = document.getElementById("remoteVideo");
          remoteVideo.srcObject = event.streams[0];
          logDebug('Set remote stream to main remote video');
        }

        // Add to participant list
        addRemoteParticipant(remoteUserId, "User " + remoteUserId, event.streams[0]);
      };

      // ICE candidate
      pc.onicecandidate = (event) => {
        if (event.candidate) {
          logDebug(`Generated ICE candidate for user ${remoteUserId}`);
          sendSignal(currentMeetingId, currentUserId, "candidate", null, event.candidate, remoteUserId);
        }
      };

      // Connection state changes
      pc.oniceconnectionstatechange = () => {
        logDebug(`ICE connection state changed to ${pc.iceConnectionState} for user ${remoteUserId}`);
      };

      peers[remoteUserId] = pc;
      return pc;
    }

    // Virtual Meeting Test
    let localStream = null;
    let screenStream = null;
    let peers = {}; // Peer connections
    let recording = false;
    let recorder = null;
    let recordedChunks = [];
    let currentModal = null; // "test" or "join"
    let pollingInterval = null; // For checking incoming signals

    // STUN server config
    const config = {
      iceServers: [{
        urls: "stun:stun.l.google.com:19302"
      }]
    };

    // Function to join the meeting
    async function openJoinMeeting(meeting) {
      currentModal = "join";
      currentMeetingId = meeting.meeting_id;
      const userId = parseInt(currentUserId);
      const creatorId = parseInt(meeting.created_by_id);

      // Check if user is invited
      if (userId !== creatorId && !meeting.participants.some(p => parseInt(p.id) === userId)) {
        showPopup("You are not invited to this meeting.");
        return;
      }

      document.getElementById("joinMeetingModal").style.display = "block";

      // Use initializeMedia instead of direct getUserMedia
      localStream = await initializeMedia();
      if (!localStream) {
        // Function will have already shown error message
        return;
      }

      // Turn off both video and audio initially
      localStream.getVideoTracks()[0].enabled = false;
      localStream.getAudioTracks()[0].enabled = false;

      // Display local video
      const localVideo = document.getElementById("localVideo");
      localVideo.srcObject = localStream;

      // Start polling for signals
      startSignalPolling(currentMeetingId, userId);

      // Notify others that we've joined
      sendSignal(currentMeetingId, userId, "join", null, null);
    }

    // Open modal for testing
    async function openTestMeeting(meeting) {
      currentModal = "test";
      currentMeetingId = meeting?.meeting_id || null;
      document.getElementById("testMeetingModal").style.display = "block";

      // Use initializeMedia instead of direct getUserMedia
      localStream = await initializeMedia();
      if (!localStream) {
        // Function will have already shown error message
        return;
      }

      // Turn off both video and audio initially
      localStream.getVideoTracks()[0].enabled = false;
      localStream.getAudioTracks()[0].enabled = false;

      const myVideo = document.querySelector("#testMeetingModal .active-speaker video");
      myVideo.srcObject = localStream;

      // Update label
      document.getElementById("localUserLabel").textContent = currentUsername + " (You)";

      // If this is a real meeting (not just testing), set up connections
      if (meeting && meeting.meeting_id) {
        // Start polling for signals
        startSignalPolling(meeting.meeting_id, currentUserId);
        // Notify others
        sendSignal(meeting.meeting_id, currentUserId, "join", null, null);
      }
    }

    // Handle signaling - send
    function sendSignal(meetingId, senderId, type, sdp, candidate, receiverId = 0) {
      const data = new FormData();
      data.append('meeting_id', meetingId);
      data.append('sender_id', senderId);
      data.append('type', type);
      data.append('receiver_id', receiverId);

      if (sdp) data.append('sdp', JSON.stringify(sdp));
      if (candidate) data.append('candidate', JSON.stringify(candidate));

      logDebug(`Sending signal: ${type} to ${receiverId || 'all'}`);

      fetch("send_signal.php", {
          method: "POST",
          body: data
        })
        .then(response => response.json())
        .then(result => {
          if (!result.success) {
            logDebug(`Signal send error: ${result.error || 'unknown'}`);
          }
        })
        .catch(err => logDebug(`Signal send failed: ${err.message}`));
    }

    // Start polling for signals
    function startSignalPolling(meetingId, userId) {
      // Clear any existing polling
      if (pollingInterval) clearInterval(pollingInterval);

      logDebug(`Starting signal polling for meeting ${meetingId}, user ${userId}`);

      // Set up polling
      pollingInterval = setInterval(() => {
        fetch(`gets_signal.php?meeting_id=${meetingId}&user_id=${userId}`)
          .then(res => res.json())
          .then(signals => {
            if (signals && signals.length) {
              logDebug(`Received ${signals.length} signals`);
              signals.forEach(processSignal);
            }
          })
          .catch(err => logDebug(`Error polling signals: ${err.message}`));
      }, 1000);
    }

    // Process incoming signals
    async function processSignal(signal) {
      const senderId = parseInt(signal.sender_id);
      logDebug(`Processing signal: ${signal.type} from user ${senderId}`);

      try {
        if (signal.type === "join") {
          // Someone joined, create peer and send offer
          logDebug(`User ${senderId} joined, creating peer connection`);
          const pc = createPeer(senderId);
          const offer = await pc.createOffer();
          await pc.setLocalDescription(offer);
          sendSignal(currentMeetingId, currentUserId, "offer", pc.localDescription, null, senderId);
        } else if (signal.type === "offer") {
          // Received offer, create answer
          logDebug(`Received offer from user ${senderId}`);
          const pc = createPeer(senderId);
          const offer = JSON.parse(signal.sdp);
          await pc.setRemoteDescription(new RTCSessionDescription(offer));
          const answer = await pc.createAnswer();
          await pc.setLocalDescription(answer);
          sendSignal(currentMeetingId, currentUserId, "answer", pc.localDescription, null, senderId);
        } else if (signal.type === "answer") {
          // Received answer to our offer
          logDebug(`Received answer from user ${senderId}`);
          const pc = peers[senderId];
          if (pc) {
            const answer = JSON.parse(signal.sdp);
            await pc.setRemoteDescription(new RTCSessionDescription(answer));
          } else {
            logDebug(`No peer connection found for user ${senderId}`);
          }
        } else if (signal.type === "candidate") {
          // Received ICE candidate
          const pc = peers[senderId];
          if (pc) {
            const candidate = JSON.parse(signal.candidate);
            await pc.addIceCandidate(new RTCIceCandidate(candidate));
            logDebug(`Added ICE candidate from user ${senderId}`);
          } else {
            logDebug(`Cannot add ICE candidate, no peer connection for user ${senderId}`);
          }
        }
      } catch (err) {
        logDebug(`Error processing signal: ${err.message}`);
      }
    }

    // Create Peer Connection
    function createPeer(remoteUserId) {
      if (peers[remoteUserId]) {
        logDebug(`Reusing existing peer connection for user ${remoteUserId}`);
        return peers[remoteUserId];
      }

      logDebug(`Creating new peer connection for user ${remoteUserId}`);
      const pc = new RTCPeerConnection(config);

      // Add local tracks if we have them
      if (localStream) {
        localStream.getTracks().forEach(track => {
          logDebug(`Adding ${track.kind} track to peer connection`);
          pc.addTrack(track, localStream);
        });
      } else {
        logDebug('No local stream to add to peer connection');
      }

      // Remote track
      pc.ontrack = (event) => {
        logDebug(`Received ${event.track.kind} track from user ${remoteUserId}`);

        if (currentModal === "join") {
          const remoteVideo = document.getElementById("remoteVideo");
          remoteVideo.srcObject = event.streams[0];
          logDebug('Set remote stream to main remote video');
        }

        // Add to participant list
        addRemoteParticipant(remoteUserId, "User " + remoteUserId, event.streams[0]);
      };

      // ICE candidate
      pc.onicecandidate = (event) => {
        if (event.candidate) {
          logDebug(`Generated ICE candidate for user ${remoteUserId}`);
          sendSignal(currentMeetingId, currentUserId, "candidate", null, event.candidate, remoteUserId);
        }
      };

      // Connection state changes
      pc.oniceconnectionstatechange = () => {
        logDebug(`ICE connection state changed to ${pc.iceConnectionState} for user ${remoteUserId}`);
      };

      peers[remoteUserId] = pc;
      return pc;
    }

    // Add "Test WebRTC Connection" button to meeting cards
    function addTestButton() {
      document.querySelectorAll('.meeting-actions').forEach(actions => {
        if (!actions.querySelector('.btn.webrtc-test')) {
          const testBtn = document.createElement('button');
          testBtn.className = 'btn webrtc-test';
          testBtn.textContent = 'Test WebRTC';
          testBtn.onclick = function() {
            window.open('webrtc_test.php', '_blank');
          };
          actions.appendChild(testBtn);
        }
      });
    }

    // Call addTestButton after loadMeetings
    const originalLoadMeetings = loadMeetings;
    loadMeetings = function() {
      originalLoadMeetings();
      setTimeout(addTestButton, 500);
    };

    // Close modal
    function closeModal(id) {
      const modal = document.getElementById(id);
      if (modal) {
        modal.style.display = "none";
      }

      // Clear polling interval
      if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
      }

      // Stop streams if available
      if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localStream = null;
      }

      if (screenStream) {
        screenStream.getTracks().forEach(track => track.stop());
        screenStream = null;
      }

      // Clear peer connections
      Object.values(peers).forEach(pc => pc.close());
      peers = {};
    }

    // Modal Helpers 
    function openModal() {
      document.getElementById("meetingModal").style.display = "block";
    }

    function openHistory() {
      // Placeholder function for history functionality
      alert("History functionality will be implemented here");
    }

    // Show toast message
    function showToast(message, duration = 1500) {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.classList.add('show');

      setTimeout(() => {
        toast.classList.remove('show');
      }, duration);
    }

    // Toggle Mic
    function toggleMic() {
      if (!localStream || !localStream.getAudioTracks().length) return;

      const track = localStream.getAudioTracks()[0];
      track.enabled = !track.enabled;
      showToast(track.enabled ? "Microphone On" : "Microphone Off");
    }

    // Toggle Camera
    function toggleCamera() {
      if (!localStream || !localStream.getVideoTracks().length) return;

      const track = localStream.getVideoTracks()[0];
      track.enabled = !track.enabled;
      showToast(track.enabled ? "Camera On" : "Camera Off");
    }

    // Screen Share
    async function startScreenShare() {
      if (!localStream) return;
      try {
        screenStream = await navigator.mediaDevices.getDisplayMedia({
          video: true
        });
        const screenTrack = screenStream.getVideoTracks()[0];

        // Replace track for all peers
        for (let peerId in peers) {
          const sender = peers[peerId].getSenders().find(s => s.track.kind === "video");
          if (sender) sender.replaceTrack(screenTrack);
        }

        // Update local video
        const myVideo = currentModal === "join" ?
          document.getElementById("localVideo") :
          document.querySelector("#testMeetingModal .active-speaker video");

        myVideo.srcObject = screenStream;

        screenTrack.onended = () => stopScreenShare();

        showToast("Screen sharing started");
      } catch (err) {
        console.error("Screen share error:", err);
        showToast("Failed to share screen");
      }
    }

    function stopScreenShare() {
      if (!localStream || !screenStream) return;

      const videoTrack = localStream.getVideoTracks()[0];
      for (let peerId in peers) {
        const sender = peers[peerId].getSenders().find(s => s.track.kind === "video");
        if (sender) sender.replaceTrack(videoTrack);
      }

      const myVideo = currentModal === "join" ?
        document.getElementById("localVideo") :
        document.querySelector("#testMeetingModal .active-speaker video");

      myVideo.srcObject = localStream;

      if (screenStream) {
        screenStream.getTracks().forEach(track => track.stop());
        screenStream = null;
      }

      showToast("Screen sharing stopped");
    }

    // Recording
    async function toggleRecording() {
      if (!localStream) {
        showToast("No stream available to record");
        return;
      }

      const btn = currentModal === "test" ? document.getElementById("recordBtn") : document.getElementById("recordBtn2");
      const indicator = document.getElementById("recordingIndicator");

      if (!recording) {
        try {
          recorder = new MediaRecorder(localStream);
          recordedChunks = [];

          recorder.ondataavailable = e => {
            if (e.data.size > 0) recordedChunks.push(e.data);
          };

          recorder.start();
          btn.innerHTML = '<i class="fas fa-stop"></i><span>Stop</span>';
          recording = true;
          indicator.style.display = "block";
          showToast("Recording started");
        } catch (err) {
          console.error("Recording error:", err);
          showToast("Failed to start recording");
        }
      } else {
        recorder.stop();
        btn.innerHTML = '<i class="fas fa-circle"></i><span>Record</span>';
        recording = false;
        indicator.style.display = "none";

        recorder.onstop = async () => {
          const blob = new Blob(recordedChunks, {
            type: "video/webm"
          });
          const url = URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = `meeting_recording_${Date.now()}.webm`;
          a.click();
          showToast("Recording saved!");
        };
      }
    }

    // Chat
    async function openChat(meetingId) {
      if (!meetingId) return;

      const panel = currentModal === "test" ?
        document.getElementById("chatPanel-test") :
        document.getElementById("chatPanel");

      panel.classList.toggle("open");

      if (panel.classList.contains("open")) {
        loadChat(meetingId);
        if (!panel.dataset.intervalSet) {
          setInterval(() => loadChat(meetingId), 1000);
          panel.dataset.intervalSet = true;
        }
      }
    }

    async function sendChat(meetingId) {
      if (!meetingId) return;

      const inputId = currentModal === "test" ? "chatInput-test" : "chatInput";
      const input = document.getElementById(inputId);
      const message = input.value.trim();

      if (!message) return;

      try {
        await fetch("save_chat.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            meeting_id: meetingId,
            message
          })
        });

        input.value = "";
        loadChat(meetingId);
      } catch (err) {
        console.error("Chat send error:", err);
        showToast("Failed to send message");
      }
    }

    async function loadChat(meetingId) {
      if (!meetingId) return;

      const chatBoxId = currentModal === "test" ? "chatBox-test" : "chatBox";
      const chatBox = document.getElementById(chatBoxId);

      try {
        const res = await fetch("get_chat.php?meeting_id=" + meetingId);
        const data = await res.json();

        if (data.success) {
          chatBox.innerHTML = "";
          data.messages.forEach(msg => {
            const msgEl = document.createElement("div");
            msgEl.className = "chat-message";
            msgEl.textContent = msg.full_name + ": " + msg.message;
            chatBox.appendChild(msgEl);
          });
          chatBox.scrollTop = chatBox.scrollHeight;
        }
      } catch (err) {
        console.error("Chat load error:", err);
      }
    }
  </script>
  <footer>
    <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
  </footer>
</body>

</html>