const apiBase = 'api';
let currentUser = null;
let currentDate = null;

// Helpers
const showView = (viewId) => {
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    document.getElementById(viewId).classList.add('active');
};

const showToast = (message, type = 'success') => {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type} show`;
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
};

const formatDate = (date) => {
    return date.toISOString().split('T')[0];
};

const formatDayName = (date) => {
    return new Intl.DateTimeFormat('pl-PL', { weekday: 'short' }).format(date);
};

const formatDayDate = (date) => {
    return new Intl.DateTimeFormat('pl-PL', { day: 'numeric', month: 'short' }).format(date);
};

const getWorkingDays = (startDate, numDays) => {
    const days = [];
    let currentDate = new Date(startDate);
    
    // Always include today, even if weekend?
    // Let's say if today is weekend, we include it, but then +2 working days.
    // Actually, usually you book for working days. Let's just find the next N working days including today if it's a working day.
    
    while (days.length < numDays) {
        const dayOfWeek = currentDate.getDay();
        if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Skip Sunday (0) and Saturday (6)
            days.push(new Date(currentDate));
        }
        currentDate.setDate(currentDate.getDate() + 1);
    }
    return days;
};

// API Calls
const fetchAPI = async (endpoint, options = {}) => {
    const res = await fetch(`${apiBase}/${endpoint}`, options);
    const data = await res.json();
    if (!res.ok) {
        throw new Error(data.error || 'Wystąpił błąd');
    }
    return data;
};

// Auth
const checkAuth = async () => {
    try {
        const data = await fetchAPI('me.php');
        if (data.user) {
            currentUser = data.user;
            document.getElementById('user-name').textContent = currentUser.name;
            if (currentUser.is_admin == 1) {
                document.getElementById('admin-panel-btn').style.display = 'block';
            } else {
                document.getElementById('admin-panel-btn').style.display = 'none';
            }
            initDashboard();
        } else {
            showView('login-view');
        }
    } catch (e) {
        showView('login-view');
    }
};

document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorEl = document.getElementById('login-error');
    
    try {
        await fetchAPI('login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        errorEl.textContent = '';
        checkAuth();
    } catch (e) {
        errorEl.textContent = e.message;
    }
});

document.getElementById('logout-btn').addEventListener('click', async () => {
    try {
        await fetchAPI('logout.php');
        currentUser = null;
        showView('login-view');
        document.getElementById('email').value = '';
        document.getElementById('password').value = '';
    } catch (e) {
        showToast(e.message, 'error');
    }
});

// Dashboard Init
let workingDaysList = []; // store for index checking

const initDashboard = () => {
    showView('dashboard-view');
    const today = new Date();
    workingDaysList = getWorkingDays(today, 20); // 20 working days
    
    const tabsContainer = document.getElementById('date-tabs');
    tabsContainer.innerHTML = '';
    
    workingDaysList.forEach((dateObj, index) => {
        const dateStr = formatDate(dateObj);
        const tab = document.createElement('div');
        tab.className = `date-tab ${index === 0 ? 'active' : ''}`;
        tab.dataset.date = dateStr;
        tab.dataset.index = index;
        tab.innerHTML = `
            <span class="day-name">${formatDayName(dateObj)}</span>
            <span class="day-date">${formatDayDate(dateObj)}</span>
        `;
        
        tab.addEventListener('click', () => {
            document.querySelectorAll('.date-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            loadSpots(dateStr, index);
        });
        
        tabsContainer.appendChild(tab);
    });
    
    // Load first day
    loadSpots(formatDate(workingDaysList[0]), 0);
};

// Load Spots
let currentDayIndex = 0;
const loadSpots = async (dateStr, dayIndex) => {
    currentDate = dateStr;
    currentDayIndex = dayIndex;
    document.getElementById('current-selected-date').textContent = dateStr;
    const grid = document.getElementById('spots-grid');
    grid.innerHTML = '<p>Ładowanie...</p>';
    
    try {
        const data = await fetchAPI(`get_status.php?date=${dateStr}`);
        renderSpots(data.spots);
    } catch (e) {
        grid.innerHTML = `<p class="error-message">${e.message}</p>`;
    }
};

const renderSpots = (spots) => {
    const grid = document.getElementById('spots-grid');
    grid.innerHTML = '';
    
    spots.forEach(spot => {
        const isMyAssignedSpot = spot.owner_id === currentUser.id;
        const isBookedByMe = spot.booked_by_id === currentUser.id;
        
        let cardClass = 'spot-card ';
        let statusBadge = '';
        let infoHtml = '';
        let actionBtn = '';
        
        if (isMyAssignedSpot) {
            if (spot.is_released) {
                cardClass += 'spot-available';
                statusBadge = 'Zwolnione przez Ciebie';
                infoHtml = `<p><strong>${spot.spot_name}</strong> powraca do puli.</p>`;
                if (!spot.booked_by_id) {
                    actionBtn = `<button class="btn btn-outline btn-sm" onclick="cancelRelease()">Cofnij zwolnienie</button>`;
                } else {
                    infoHtml += `<p>Zarezerwowane przez: <strong>${spot.booked_by_name}</strong></p>`;
                }
            } else {
                cardClass += 'spot-yours';
                statusBadge = 'Twoje miejsce';
                infoHtml = `<p><strong>${spot.spot_name}</strong> - Twoje na wyłączność.</p>`;
                actionBtn = `<button class="btn btn-primary btn-sm" onclick="releaseSpot()">Zwolnij na ten dzień</button>`;
            }
        } else if (isBookedByMe) {
            cardClass += 'spot-yours';
            statusBadge = 'Zarezerwowane przez Ciebie';
            infoHtml = `<p>Właściciel: ${spot.owner_name || 'Brak (Wspólne)'}</p>`;
            actionBtn = `<button class="btn btn-outline btn-sm" onclick="cancelBooking()">Anuluj rezerwację</button>`;
        } else {
            if (spot.status === 'available') {
                cardClass += 'spot-available';
                statusBadge = 'Dostępne';
                infoHtml = `<p>Właściciel: ${spot.owner_name || 'Brak (Wspólne)'}</p>`;
                
                // Can only book up to 2 working days ahead (index <= 2, since index 0 is today, 1 is tmrw, 2 is day after)
                if (currentDayIndex <= 2) {
                    actionBtn = `<button class="btn btn-primary btn-sm" onclick="bookSpot(${spot.number})">Rezerwuj</button>`;
                } else {
                    actionBtn = `<span style="font-size: 0.8rem; color: var(--text-muted);">Rezerwacja niedostępna na tak odległy termin</span>`;
                }
            } else if (spot.status === 'booked') {
                cardClass += 'spot-occupied';
                statusBadge = 'Zarezerwowane';
                infoHtml = `<p>Zarezerwowane przez: <strong>${spot.booked_by_name}</strong></p>`;
            } else {
                cardClass += 'spot-occupied';
                statusBadge = 'Zajęte';
                infoHtml = `<p>Właściciel: <strong>${spot.owner_name}</strong></p>`;
            }
        }
        
        grid.innerHTML += `
            <div class="${cardClass}">
                <div class="spot-header">
                    <div class="spot-number" style="font-size: 1.5rem;">${spot.spot_name}</div>
                    <div class="spot-status-badge">${statusBadge}</div>
                </div>
                <div class="spot-info">${infoHtml}</div>
                <div>${actionBtn}</div>
            </div>
        `;
    });
};

// Actions
window.releaseSpot = async () => {
    try {
        await fetchAPI('release_spot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ date: currentDate })
        });
        showToast('Miejsce zwolnione');
        loadSpots(currentDate);
    } catch (e) {
        showToast(e.message, 'error');
    }
};

window.cancelRelease = async () => {
    try {
        await fetchAPI('cancel_release.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ date: currentDate })
        });
        showToast('Cofnięto zwolnienie');
        loadSpots(currentDate);
    } catch (e) {
        showToast(e.message, 'error');
    }
};

window.bookSpot = async (spotNumber) => {
    try {
        await fetchAPI('book_spot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ date: currentDate, spot_number: spotNumber })
        });
        showToast('Miejsce zarezerwowane!');
        loadSpots(currentDate);
    } catch (e) {
        showToast(e.message, 'error');
    }
};

window.cancelBooking = async () => {
    try {
        await fetchAPI('cancel_booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ date: currentDate })
        });
        showToast('Rezerwacja anulowana');
        loadSpots(currentDate);
    } catch (e) {
        showToast(e.message, 'error');
    }
};

// Admin Panel Logic
document.getElementById('admin-panel-btn').addEventListener('click', () => {
    showView('admin-view');
    loadAdminUsers();
});

window.switchAdminTab = (tab) => {
    document.querySelectorAll('.admin-tabs .date-tab').forEach(t => t.classList.remove('active'));
    document.getElementById(`tab-${tab}`).classList.add('active');
    
    document.querySelectorAll('.admin-section').forEach(s => s.style.display = 'none');
    document.getElementById(`admin-${tab}-section`).style.display = 'block';

    if (tab === 'users') loadAdminUsers();
    if (tab === 'spots') loadAdminSpots();
};

let allAdminSpots = [];
let allAdminUsers = [];

const loadAdminSpots = async () => {
    try {
        const data = await fetchAPI('admin/spots.php');
        allAdminSpots = data.spots;
        
        const tbody = document.querySelector('#spots-table tbody');
        tbody.innerHTML = '';
        allAdminSpots.forEach(s => {
            tbody.innerHTML += `
                <tr>
                    <td>${s.number}</td>
                    <td>${s.name}</td>
                    <td>
                        <button class="btn btn-sm btn-outline" onclick="openSpotModal(${s.number})">Edytuj</button>
                    </td>
                </tr>
            `;
        });
        
        // Update select options in user modal
        const select = document.getElementById('edit-user-spot');
        select.innerHTML = '<option value="">Brak (Pula ogólna)</option>';
        allAdminSpots.forEach(s => {
            select.innerHTML += `<option value="${s.number}">${s.name}</option>`;
        });

    } catch (e) {
        showToast(e.message, 'error');
    }
};

window.openSpotModal = (number) => {
    const spot = allAdminSpots.find(s => s.number == number);
    if (!spot) return;
    
    document.getElementById('spot-modal').style.display = 'flex';
    document.getElementById('edit-spot-number').value = spot.number;
    document.getElementById('edit-spot-name').value = spot.name;
};

window.closeSpotModal = () => {
    document.getElementById('spot-modal').style.display = 'none';
};

document.getElementById('edit-spot-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const number = document.getElementById('edit-spot-number').value;
    const name = document.getElementById('edit-spot-name').value;
    
    try {
        await fetchAPI('admin/spots.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ number, name })
        });
        showToast('Zapisano miejsce');
        closeSpotModal();
        loadAdminSpots();
    } catch (e) {
        showToast(e.message, 'error');
    }
});

const loadAdminUsers = async () => {
    try {
        await loadAdminSpots(); // need spots for select
        const data = await fetchAPI('admin/users.php');
        allAdminUsers = data.users;
        
        const tbody = document.querySelector('#users-table tbody');
        tbody.innerHTML = '';
        allAdminUsers.forEach(u => {
            const spotName = u.assigned_spot ? (allAdminSpots.find(s => s.number == u.assigned_spot)?.name || `Nr ${u.assigned_spot}`) : '<span style="color:var(--text-muted)">Brak</span>';
            tbody.innerHTML += `
                <tr>
                    <td>${u.id}</td>
                    <td>${u.name}</td>
                    <td>${u.email}</td>
                    <td>${spotName}</td>
                    <td>${u.is_admin ? '<span style="color:#10b981">Tak</span>' : 'Nie'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline" onclick="openUserModal(${u.id})">Edytuj</button>
                    </td>
                </tr>
            `;
        });
    } catch (e) {
        showToast(e.message, 'error');
    }
};

window.openUserModal = (id = null) => {
    document.getElementById('user-modal').style.display = 'flex';
    document.getElementById('edit-user-id').value = id || '';
    
    if (id) {
        const user = allAdminUsers.find(u => u.id == id);
        document.getElementById('modal-title').textContent = 'Edytuj Użytkownika';
        document.getElementById('edit-user-name').value = user.name;
        document.getElementById('edit-user-email').value = user.email;
        document.getElementById('edit-user-email').disabled = true; // disable email edit
        document.getElementById('group-email').style.display = 'none';
        document.getElementById('label-password').textContent = 'Nowe Hasło (zostaw puste by nie zmieniać)';
        document.getElementById('edit-user-password').required = false;
        document.getElementById('edit-user-password').value = '';
        
        document.getElementById('edit-user-spot').value = user.assigned_spot || '';
        document.getElementById('edit-user-admin').checked = user.is_admin == 1;
    } else {
        document.getElementById('modal-title').textContent = 'Dodaj Użytkownika';
        document.getElementById('user-form').reset();
        document.getElementById('edit-user-email').disabled = false;
        document.getElementById('group-email').style.display = 'block';
        document.getElementById('label-password').textContent = 'Hasło';
        document.getElementById('edit-user-password').required = true;
        document.getElementById('edit-user-password').value = '';
    }
};

window.closeUserModal = () => {
    document.getElementById('user-modal').style.display = 'none';
};

document.getElementById('user-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('edit-user-id').value;
    const payload = {
        id: id || undefined,
        name: document.getElementById('edit-user-name').value,
        assigned_spot: document.getElementById('edit-user-spot').value || null,
        is_admin: document.getElementById('edit-user-admin').checked ? 1 : 0
    };

    const passwordVal = document.getElementById('edit-user-password').value;
    if (passwordVal) {
        payload.password = passwordVal;
    }

    if (!id) {
        payload.email = document.getElementById('edit-user-email').value;
    }

    try {
        await fetchAPI('admin/users.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        showToast(id ? 'Zapisano zmiany' : 'Utworzono użytkownika');
        closeUserModal();
        loadAdminUsers();
    } catch (e) {
        showToast(e.message, 'error');
    }
});

document.getElementById('add-spot-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const number = document.getElementById('new-spot-number').value;
    const name = document.getElementById('new-spot-name').value;
    try {
        await fetchAPI('admin/spots.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ number, name })
        });
        showToast('Dodano miejsce');
        document.getElementById('add-spot-form').reset();
        loadAdminSpots();
    } catch (e) {
        showToast(e.message, 'error');
    }
});

// Boot
checkAuth();
