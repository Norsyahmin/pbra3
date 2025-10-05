<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Meeting History</title>
  <link rel="stylesheet" href="virtualmeeting.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body onload="loadHistoryMeetings()">
  <?php include '../includes/navbar.php';

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
    <h1 style="font-size: 30px;">Meeting History</h1>
  </div>

  <div class="virtual-carousel">
    <div class="virtual-header">
      <div class="title">
        <h1 style="font-size: 25px;">Past Meetings</h1>
      </div>
      <div style="display: flex; justify-content: space-between; flex-grow: 1; margin-left: 20px;">
        <button class="add-virtual-toggle" onclick="window.location.href='virtualmeeting.php'">
          Back to Meetings
        </button>
      </div>
    </div>
  </div>

  <div id="historyContainer"></div>

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

  <!-- Success Notification -->
  <div id="successPopup" class="popup-overlay" style="display: none;">
    <div class="popup-content">
      <h3 id="successMessage">Meeting has been successfully deleted!</h3>
      <div class="popup-actions">
        <button id="successOk" class="btn-yes">OK</button>
      </div>
    </div>
  </div>
  <script>
    function showPopup(message) {
      document.getElementById("successMessage").innerText = message;
      document.getElementById("successPopup").style.display = "flex";
    }
  </script>

  <script>
    const currentUserId = <?php echo $_SESSION['id']; ?>;
    const currentUsername = "<?php echo htmlspecialchars($_SESSION['full_name']); ?>";
    let invitedParticipants = [];
    let currentMeetingId = null;

    // Load Past Meetings
    function loadHistoryMeetings() {
      fetch("get_past_meetings.php")
        .then(res => res.json())
        .then(response => {
          if (!response.success) {
            alert("Error loading meetings: " + response.message);
            return;
          }

          let container = document.getElementById("historyContainer");
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

          if (filteredMeetings.length === 0) {
            container.innerHTML = `
              <div class="no-meetings">
                <p>No past meetings found that you participated in.</p>
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
                  <tr><td><strong>Duration:</strong></td><td>${estimatedTime}</td></tr>
                  <tr><td><strong>Participants:</strong></td><td>${participants}</td></tr>
                  <tr><td><strong>Created By:</strong></td><td>${meeting.created_by}</td></tr>
                </table>
                <div class="meeting-actions">
                <?php if ($is_admin): ?>
                  <button class="btn delete" data-id="${meeting.meeting_id}">Delete</button>
                  <button class="btn edit" onclick='openEditMeeting(${JSON.stringify(meeting)})'>Edit</button>
                <?php else: ?>
                  ${isCreator ? `<button class="btn delete" data-id="${meeting.meeting_id}">Delete</button>
                  <button class="btn edit" onclick='openEditMeeting(${JSON.stringify(meeting)})'>Edit</button>` : ''}
                <?php endif; ?>
                </div>
              </div>`;
          });
        })
        .catch(err => console.error(err));
    }

    // Edit Meeting functionality
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
            loadHistoryMeetings();
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

    // Search participants for edit form
    document.addEventListener('DOMContentLoaded', function() {
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
    });

    function addToEditInvited(item) {
      if (invitedParticipants.some(p => p.id === item.user_id)) {
        showPopup(item.name + " has already been invited.");
        return;
      }

      invitedParticipants.push({
        id: item.user_id,
        name: item.name,
        email: item.email
      });

      renderInvitedParticipants("editInvitedList");
    }

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
  </script>
  <footer>
    <p>&copy; 2025 Politeknik Brunei Role Appointment (PbRA). All rights reserved.</p>
  </footer>
</body>

</html>