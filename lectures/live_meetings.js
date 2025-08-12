class LiveMeetings {
    constructor() {
        this.liveSection = document.getElementById('liveMeetingsSection');
        if (!this.liveSection) {
            this.createLiveSection();
        }
        
        this.initEventListeners();
        this.loadLiveMeetings();
        setInterval(() => this.loadLiveMeetings(), 30000); // Refresh every 30 seconds
    }
    
    createLiveSection() {
        const html = `
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Live Meetings</h5>
                <div id="liveMeetingsSection">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
        
        // Insert after the main meetings table
        document.querySelector('#meetingTable').insertAdjacentHTML('afterend', html);
        this.liveSection = document.getElementById('liveMeetingsSection');
    }
    
    initEventListeners() {
        // Delegate events for dynamically added buttons
        document.body.addEventListener('click', (e) => {
            if (e.target.classList.contains('end-meeting-btn')) {
                this.endMeeting(e.target.dataset.id);
            }
            
            if (e.target.classList.contains('start-live-btn')) {
                this.startMeeting(
                    e.target.dataset.id, 
                    e.target.dataset.link,
                    e.target
                );
            }
        });
    }
    
    async loadLiveMeetings() {
    try {
        const response = await fetch('live_meetings.php?action=get_live_meetings');
        const meetings = await response.json();
        
        if (meetings.length === 0) {
            this.liveSection.innerHTML = `
            <div class="alert alert-info text-center py-3">
                <i class="fas fa-video-slash me-2"></i> No live meetings currently
            </div>`;
            return;
        }
        
        let html = '<div class="row g-4">';
        meetings.forEach(meeting => {
            const meetingDateTime = new Date(`${meeting.date}T${meeting.start_time}`);
            const meetingTime = meetingDateTime.toLocaleString();
            const expiryText = this.calculateExpiryText(meeting);
            const statusBadge = meeting.status === 'active' ? 
                '<span class="badge bg-success"><i class="fas fa-circle me-1"></i> Live</span>' : 
                '<span class="badge bg-success"><i class="fas fa-circle me-1"></i> Live Now</span>';
            
            // Format creator info (handle null values)
            const creatorName = meeting.created_by ;
            const creatorRole = meeting.role ? `(${meeting.role})` : '';
            
            html += `
            <div class="col-md-6 col-lg-4">
                <div class="card border-primary h-100 shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between">
                        <div>
                            <i class="fas fa-video me-2 mt-2"></i>
                            <strong>${meeting.title}</strong>
                        </div>
                        ${statusBadge}
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-book me-2 text-muted"></i>
                                <span class="text-truncate">${meeting.subject || 'No subject'}</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-calendar-day me-2 text-muted"></i>
                                <span>${meetingTime}</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-user-tie me-2 text-muted"></i>
                                <span>${creatorName} ${creatorRole}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock me-2 text-muted"></i>
                                <span>${expiryText}</span>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-2">
                            <a href="${meeting.zoom_link}" target="_blank" class="btn btn-success btn-sm">
                                <i class="fas fa-video me-1"></i> Join Meeting
                            </a>
                            <button class="btn btn-danger btn-sm end-meeting-btn" data-id="${meeting.id}">
                                <i class="fas fa-stop me-1"></i> End
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        html += '</div>';
        
        this.liveSection.innerHTML = html;
    } catch (error) {
        console.error('Error loading live meetings:', error);
        this.liveSection.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Error loading live meetings. Please refresh the page.
        </div>`;
    }
}

// Add this new method to calculate expiry text
calculateExpiryText(meeting) {
    if (!meeting.link_expiry_status || meeting.link_expiry_status === 'permanent') {
        return 'No expiry';
    }

    const meetingStart = new Date(`${meeting.date}T${meeting.start_time}`);
    if (isNaN(meetingStart.getTime())) {
        return 'Invalid date';
    }

    // Calculate expiry time based on link_expiry_status
    let expiryTime = new Date(meetingStart);
    const expirySetting = meeting.link_expiry_status.toLowerCase();

    if (expirySetting.endsWith('h')) {
        const hours = parseInt(expirySetting);
        expiryTime.setHours(expiryTime.getHours() + hours);
    } 
    else if (expirySetting.endsWith('d')) {
        const days = parseInt(expirySetting);
        expiryTime.setDate(expiryTime.getDate() + days);
    }
    else if (expirySetting.endsWith('m')) {
        const months = parseInt(expirySetting);
        expiryTime.setMonth(expiryTime.getMonth() + months);
    }
    else {
        return 'Expires: ' + meeting.link_expiry_status;
    }

    // Format the expiry time
    const now = new Date();
    if (expiryTime < now) {
        return '<span class="text-danger">Expired</span>';
    }

    // Format as "Expires: Aug 6, 10:00 PM"
    const options = { 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    };
    return `Expires: ${expiryTime.toLocaleDateString('en-US', options)}`;
}
    
    async startMeeting(meetingId, zoomLink, button) {
        if (!confirm('Start this meeting and mark it as live?')) return;
        
        try {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Starting...';
            
            const response = await fetch('live_meetings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'start_meeting',
                    meeting_id: meetingId,
                    zoom_link: zoomLink
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                if (zoomLink) {
                    window.open(zoomLink, '_blank');
                }
                this.loadLiveMeetings();
                // Refresh main meetings table if exists
                if (typeof loadMeetings === 'function') {
                    loadMeetings();
                }
            } else {
                alert(result.message || 'Failed to start meeting');
            }
        } catch (error) {
            console.error('Error starting meeting:', error);
            alert('Error starting meeting');
        } finally {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-play"></i> Start Again';
        }
    }
    
    async endMeeting(meetingId) {
        if (!confirm('Are you sure you want to end this meeting?')) return;
        
        try {
            const response = await fetch('live_meetings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'end_meeting',
                    meeting_id: meetingId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.loadLiveMeetings();
                // Refresh main meetings table if exists
                if (typeof loadMeetings === 'function') {
                    loadMeetings();
                }
            } else {
                alert(result.message || 'Failed to end meeting');
            }
        } catch (error) {
            console.error('Error ending meeting:', error);
            alert('Error ending meeting');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new LiveMeetings();
    
    // Modify existing start buttons in the main table
    document.querySelectorAll('[data-action="start-meeting"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const meetingId = btn.getAttribute('data-id');
            const zoomLink = btn.getAttribute('data-link');
            
            // Use our new startMeeting functionality
            const liveMeetings = new LiveMeetings();
            liveMeetings.startMeeting(meetingId, zoomLink, btn);
        });
    });
});