(function () {
    const STORAGE_KEY = 'timlabahn.rooms.directory.v3';
    const SESSION_KEY = 'timlabahn.rooms.session.v1';
    const USERS_KEY = 'timlabahn.rooms.users.v1';
    const FLOORS = [4, 3, 2, 1, 0];
    const STATUSES = ['available', 'occupied', 'full', 'permanent'];
    const STATUS_LABELS = {
        available: 'Available',
        occupied: 'Occupied',
        full: 'Full',
        permanent: 'Permanent'
    };
    const ROOM_TYPES = ['Conference', 'Focus', 'Meeting', 'Office', 'Lounge', 'Lab', 'Studio', 'Booth', 'War Room'];
    const OWNER_TEAMS = ['Design', 'Operations', 'Engineering', 'Finance', 'People'];
    const DEFAULT_ROOM_FEATURES = [
        'Smart board', 'Whiteboard', 'Projector', 'Tables', 'Couch', 'Video conferencing',
        'Coffee machine', 'Large TV', 'Air conditioning', 'Standing desk', 'Window view'
    ];
    const DEMO_PASSWORD = 'password';
    const PREMADE_USERS = [
        { id: 'admin', name: 'Admin User', email: 'admin@rooms.test', role: 'Admin' },
        { id: 'taylor', name: 'Taylor Reed', email: 'taylor.reed@example.com', role: 'Operations' },
        { id: 'alex', name: 'Alex Morgan', email: 'alex.morgan@example.com', role: 'Engineering' },
        { id: 'morgan', name: 'Morgan Chen', email: 'morgan.chen@example.com', role: 'Product' },
        { id: 'jamie', name: 'Jamie Patel', email: 'jamie.patel@example.com', role: 'Design' },
        { id: 'robin', name: 'Robin Fischer', email: 'robin.fischer@example.com', role: 'People' },
        { id: 'casey', name: 'Casey Nguyen', email: 'casey.nguyen@example.com', role: 'Finance' },
        { id: 'sam', name: 'Sam Walker', email: 'sam.walker@example.com', role: 'Sales' },
        { id: 'jordan', name: 'Jordan Kim', email: 'jordan.kim@example.com', role: 'Marketing' },
        { id: 'riley', name: 'Riley Weber', email: 'riley.weber@example.com', role: 'Customer success' },
        { id: 'avery', name: 'Avery Brooks', email: 'avery.brooks@example.com', role: 'Research' },
        { id: 'dana', name: 'Dana Schmidt', email: 'dana.schmidt@example.com', role: 'IT' }
    ];
    const BUILDINGS = [
        {
            id: 'hq-east',
            name: 'HQ East',
            sections: [
                { id: 'main-wing', name: 'Main Wing' },
                { id: 'executive-floor', name: 'Executive Floor' },
                { id: 'innovation-lab', name: 'Innovation Lab' }
            ]
        },
        {
            id: 'tech-hub-west',
            name: 'Tech Hub West',
            sections: [
                { id: 'main-wing', name: 'Main Wing' },
                { id: 'executive-floor', name: 'Executive Floor' },
                { id: 'innovation-lab', name: 'Innovation Lab' }
            ]
        }
    ];

    const elements = {
        root: document.getElementById('view-root'),
        globalSearch: document.getElementById('global-search'),
        search: document.getElementById('search'),
        accountNav: document.getElementById('account-nav'),
        statusLegend: document.querySelector('.status-legend'),
        roomDialog: document.getElementById('room-dialog'),
        roomForm: document.getElementById('room-form'),
        roomDialogTitle: document.getElementById('dialog-title'),
        roomId: document.getElementById('room-id'),
        roomName: document.getElementById('room-name'),
        roomBuilding: document.getElementById('room-building'),
        roomSection: document.getElementById('room-section'),
        roomFloor: document.getElementById('room-floor'),
        roomStatus: document.getElementById('room-status'),
        roomCapacity: document.getElementById('room-capacity'),
        roomOwner: document.getElementById('room-owner'),
        featureOptions: document.getElementById('feature-options'),
        otherFeature: document.getElementById('other-feature'),
        addFeature: document.getElementById('add-feature'),
        featureSuggestions: document.getElementById('feature-suggestions'),
        deleteRoom: document.getElementById('delete-room'),
        bookingDialog: document.getElementById('booking-dialog'),
        bookingForm: document.getElementById('booking-form'),
        bookingTitle: document.getElementById('booking-title'),
        bookingRoomInfo: document.getElementById('booking-room-info'),
        bookingStart: document.getElementById('booking-start'),
        bookingEnd: document.getElementById('booking-end'),
        bookingReason: document.getElementById('booking-reason'),
        bookingError: document.getElementById('booking-error'),
        toast: document.getElementById('toast')
    };

    let users = loadUsers();
    let data = loadData();
    let signedInUser = loadSession();
    let activeBuildingId = null;
    let activeSectionId = null;
    let activeFloor = 0;
    let activeView = 'buildings';
    let pendingFeatures = [];
    let toastTimer;

    function isValidRoom(room) {
        if (!room || typeof room.id !== 'string' || typeof room.name !== 'string') return false;
        const building = BUILDINGS.find((item) => item.id === room.buildingId);
        return Boolean(building && building.sections.some((section) => section.id === room.sectionId)
            && FLOORS.includes(Number(room.floor)) && STATUSES.includes(room.status));
    }

    function isValidBooking(booking, rooms) {
        return booking && typeof booking.id === 'string'
            && rooms.some((room) => room.id === booking.roomId)
            && users.some((user) => user.id === booking.userId)
            && Number.isFinite(Date.parse(booking.start))
            && Number.isFinite(Date.parse(booking.end))
            && Date.parse(booking.end) > Date.parse(booking.start);
    }

    function randomRoomFeatures() {
        const selected = DEFAULT_ROOM_FEATURES.filter(() => Math.random() < 0.31);
        if (selected.length === 0) selected.push(DEFAULT_ROOM_FEATURES[Math.floor(Math.random() * DEFAULT_ROOM_FEATURES.length)]);
        return selected;
    }

    function normalizedFeatureName(name) {
        return name.trim().toLocaleLowerCase().replace(/[^a-z0-9]/g, '');
    }

    function createFeatureCatalog(featureNames, rooms = []) {
        const catalog = new Map();
        [...DEFAULT_ROOM_FEATURES, ...(featureNames || []), ...rooms.flatMap((room) => room.features || [])]
            .forEach((name) => {
                if (typeof name !== 'string') return;
                const value = name.trim();
                const key = normalizedFeatureName(value);
                if (key && !catalog.has(key)) catalog.set(key, value);
            });
        return Array.from(catalog.values());
    }

    function editDistance(first, second) {
        const previous = Array.from({ length: second.length + 1 }, (_, index) => index);
        for (let row = 1; row <= first.length; row += 1) {
            let diagonal = previous[0];
            previous[0] = row;
            for (let column = 1; column <= second.length; column += 1) {
                const above = previous[column];
                previous[column] = Math.min(
                    previous[column] + 1,
                    previous[column - 1] + 1,
                    diagonal + (first[row - 1] === second[column - 1] ? 0 : 1)
                );
                diagonal = above;
            }
        }
        return previous[second.length];
    }

    function featureSimilarity(query, feature) {
        const normalizedQuery = normalizedFeatureName(query);
        const normalizedFeature = normalizedFeatureName(feature);
        if (!normalizedQuery || !normalizedFeature) return 0;
        return 1 - editDistance(normalizedQuery, normalizedFeature) / Math.max(normalizedQuery.length, normalizedFeature.length);
    }

    function createDemoRooms() {
        const rooms = [];
        BUILDINGS.forEach((building) => {
            building.sections.forEach((section, sectionIndex) => {
                for (let floor = 0; floor <= 4; floor += 1) {
                    const roomCount = 6 + Math.floor(Math.random() * 7);
                    for (let number = 1; number <= roomCount; number += 1) {
                        const roomNumber = `${floor === 0 ? 'E' : floor}${String(number).padStart(2, '0')}`;
                        const statusRoll = Math.random();
                        const status = statusRoll < 0.07 ? 'occupied'
                            : statusRoll < 0.12 ? 'full'
                                : statusRoll < 0.17 ? 'permanent' : 'available';
                        rooms.push({
                            id: `demo-${building.id}-${section.id}-${floor}-${number}`,
                            buildingId: building.id,
                            sectionId: section.id,
                            floor,
                            name: `${ROOM_TYPES[Math.floor(Math.random() * ROOM_TYPES.length)]} ${roomNumber}`,
                            owner: Math.random() < 0.18 ? `${OWNER_TEAMS[(number + sectionIndex) % OWNER_TEAMS.length]} Team` : '',
                            capacity: 4 + Math.floor(Math.random() * 17),
                            features: randomRoomFeatures(),
                            status
                        });
                    }
                }
            });
        });
        return rooms;
    }

    function dateTimeOffset(days, hour) {
        const value = new Date();
        value.setDate(value.getDate() + days);
        value.setHours(hour, 0, 0, 0);
        return value;
    }

    function createDemoBookings(rooms) {
        const bookings = [];
        const reasons = ['Weekly team sync', 'Sprint planning', 'Customer review', 'Interview', 'Training session', 'Focus time', 'Project kickoff', 'Quarterly planning', 'Design review', 'One-to-one'];
        PREMADE_USERS.forEach((user, userIndex) => {
            const userRooms = rooms.filter((room) => room.status !== 'permanent');
            if (!userRooms.length) return;
            const bookingCount = 1 + (userIndex % 3);
            for (let bookingIndex = 0; bookingIndex < bookingCount; bookingIndex += 1) {
                const room = userRooms[(userIndex * 11 + bookingIndex * 17) % userRooms.length];
                let start = dateTimeOffset(1 + ((userIndex + bookingIndex * 2) % 14), 8 + ((userIndex * 2 + bookingIndex * 3) % 9));
                if (start.getDay() === 0) start.setDate(start.getDate() + 1);
                if (start.getDay() === 6) start.setDate(start.getDate() + 2);
                const duration = 30 + (30 * ((userIndex + bookingIndex) % 4));
                const end = new Date(start.getTime() + duration * 60 * 1000);
                bookings.push({
                    id: `demo-booking-${user.id}-${bookingIndex + 1}`,
                    roomId: room.id,
                    userId: user.id,
                    start: start.toISOString(),
                    end: end.toISOString(),
                    reason: reasons[(userIndex + bookingIndex) % reasons.length],
                    cancelled: false,
                    demo: true
                });
            }
        });
        return bookings;
    }

    function addMissingPremadeBookings(rooms, bookings) {
        const usersWithSeededBookings = new Set(bookings.filter((booking) => booking.demo).map((booking) => booking.userId));
        const additions = createDemoBookings(rooms).filter((booking) => !usersWithSeededBookings.has(booking.userId));
        return bookings.concat(additions);
    }

    function saveData() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            return true;
        } catch (error) {
            showToast('Could not save. Check browser storage settings.');
            return false;
        }
    }

    function loadData() {
        try {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY));
            if (saved && [3, 4, 5].includes(saved.version) && Array.isArray(saved.rooms)) {
                const rooms = saved.rooms.filter(isValidRoom);
                const missingRoomFeatures = rooms.some((room) => !Array.isArray(room.features));
                rooms.forEach((room) => {
                    if (!Array.isArray(room.features)) room.features = randomRoomFeatures();
                });
                let bookings = Array.isArray(saved.bookings)
                    ? saved.bookings.filter((booking) => isValidBooking(booking, rooms))
                    : createDemoBookings(rooms);
                bookings = addMissingPremadeBookings(rooms, bookings);
                const features = createFeatureCatalog(saved.features, rooms);
                const updated = { version: 5, rooms, bookings, features };
                if (saved.version !== 5 || !Array.isArray(saved.features) || missingRoomFeatures) {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
                }
                return updated;
            }

            const previous = JSON.parse(localStorage.getItem('timlabahn.rooms.directory.v2'));
            if (previous && previous.version === 2 && Array.isArray(previous.rooms)) {
                const rooms = previous.rooms.filter(isValidRoom);
                rooms.forEach((room) => {
                    if (!Array.isArray(room.features)) room.features = randomRoomFeatures();
                });
                const migrated = { version: 5, rooms, bookings: createDemoBookings(rooms), features: createFeatureCatalog([], rooms) };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(migrated));
                return migrated;
            }
        } catch (error) {
            console.warn('Could not read saved room data.', error);
        }

        const rooms = createDemoRooms();
        const initialData = { version: 5, rooms, bookings: createDemoBookings(rooms), features: createFeatureCatalog([], rooms) };
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(initialData));
        } catch (error) {
            console.warn('Could not save demo data.', error);
        }
        return initialData;
    }

    function loadUsers() {
        try {
            const saved = JSON.parse(localStorage.getItem(USERS_KEY));
            const registered = Array.isArray(saved) ? saved.filter((user) => user
                && typeof user.id === 'string'
                && typeof user.name === 'string'
                && typeof user.email === 'string'
                && typeof user.passwordHash === 'string'
                && typeof user.passwordSalt === 'string'
                && !PREMADE_USERS.some((premade) => premade.email.toLowerCase() === user.email.toLowerCase())) : [];
            return PREMADE_USERS.concat(registered);
        } catch (error) {
            return PREMADE_USERS.slice();
        }
    }

    function saveUsers() {
        try {
            localStorage.setItem(USERS_KEY, JSON.stringify(users.filter((user) => !PREMADE_USERS.some((premade) => premade.id === user.id))));
            return true;
        } catch (error) {
            showToast('Could not save this test account in your browser.');
            return false;
        }
    }

    async function hashPassword(password, salt) {
        const input = new TextEncoder().encode(`${salt}:${password}`);
        const digest = await crypto.subtle.digest('SHA-256', input);
        return Array.from(new Uint8Array(digest), (byte) => byte.toString(16).padStart(2, '0')).join('');
    }

    function loadSession() {
        try {
            const userId = localStorage.getItem(SESSION_KEY);
            return users.find((user) => user.id === userId) || null;
        } catch (error) {
            return null;
        }
    }

    function saveSession(user) {
        try {
            if (user) localStorage.setItem(SESSION_KEY, user.id);
            else localStorage.removeItem(SESSION_KEY);
            signedInUser = user;
            return true;
        } catch (error) {
            showToast('Could not save the demo sign-in in this browser.');
            return false;
        }
    }

    function floorName(floor) {
        return Number(floor) === 0 ? 'E' : String(floor);
    }

    function roomCountForBuilding(buildingId) {
        return data.rooms.filter((room) => room.buildingId === buildingId).length;
    }

    function roomCountForSection(buildingId, sectionId) {
        return data.rooms.filter((room) => room.buildingId === buildingId && room.sectionId === sectionId).length;
    }

    function addDemoNotice(screen) {
        const notice = document.createElement('p');
        notice.className = 'demo-notice';
        notice.textContent = 'Demo limits: buildings and floors are fixed. You can add and change rooms, but cannot add buildings or floors.';
        screen.prepend(notice);
    }

    function renderLogin(mode = 'signin') {
        elements.globalSearch.hidden = true;
        elements.accountNav.replaceChildren();
        elements.statusLegend.hidden = true;
        const screen = document.createElement('section');
        screen.className = 'login-view';
        if (mode === 'register') {
            screen.innerHTML = `
                <div class="login-panel">
                    <p class="login-kicker">ROOMS · LOCAL DEMO</p>
                    <h1>Create test account</h1>
                    <p class="login-intro">Your account and bookings stay in this browser.</p>
                    <form id="register-form">
                        <label class="field-label" for="register-name">Full name</label>
                        <input class="field-input" id="register-name" name="name" maxlength="80" autocomplete="name" required>
                        <label class="field-label" for="register-email">Work email</label>
                        <input class="field-input" id="register-email" name="email" type="email" maxlength="120" autocomplete="email" required>
                        <label class="field-label" for="register-password">Password</label>
                        <input class="field-input" id="register-password" name="password" type="password" minlength="8" autocomplete="new-password" required>
                        <label class="field-label" for="register-confirm">Confirm password</label>
                        <input class="field-input" id="register-confirm" name="confirm" type="password" minlength="8" autocomplete="new-password" required>
                        <p class="login-error" id="register-error" role="alert" hidden></p>
                        <button class="save-button login-submit" type="submit">Create account</button>
                    </form>
                    <button class="login-switch" id="show-signin" type="button">Already have a test account? Sign in</button>
                    <p class="login-local-note">Demo account only. This is not secure authentication and nothing is sent to a server.</p>
                </div>`;
            elements.root.replaceChildren(screen);
            document.getElementById('register-form').addEventListener('submit', async (event) => {
                event.preventDefault();
                const name = document.getElementById('register-name').value.trim();
                const email = document.getElementById('register-email').value.trim().toLowerCase();
                const password = document.getElementById('register-password').value;
                const confirmation = document.getElementById('register-confirm').value;
                const error = document.getElementById('register-error');
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!name || !emailPattern.test(email)) error.textContent = 'Enter your name and a valid email address.';
                else if (users.some((user) => user.email.toLowerCase() === email)) error.textContent = 'An account already exists for that email.';
                else if (password.length < 8) error.textContent = 'Use at least 8 characters for this test password.';
                else if (password !== confirmation) error.textContent = 'The passwords do not match.';
                else {
                    const passwordSalt = crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random()}`;
                    let passwordHash;
                    try {
                        passwordHash = await hashPassword(password, passwordSalt);
                    } catch (hashError) {
                        error.textContent = 'This browser cannot create a local test account.';
                        return;
                    }
                    const user = {
                        id: `member-${crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random()}`}`,
                        name,
                        email,
                        role: 'Member',
                        passwordSalt,
                        passwordHash
                    };
                    users.push(user);
                    if (!saveUsers()) {
                        users = users.filter((item) => item.id !== user.id);
                        return;
                    }
                    if (!saveSession(user)) return;
                    activeView = 'buildings';
                    render();
                    showToast('Test account created');
                    return;
                }
                error.hidden = false;
            });
            document.getElementById('show-signin').addEventListener('click', () => renderLogin('signin'));
            return;
        }

        screen.innerHTML = `
            <div class="login-panel">
                <p class="login-kicker">ROOMS · LOCAL DEMO</p>
                <h1>Sign in</h1>
                <p class="login-intro">Choose an account to see its room bookings.</p>
                <form id="login-form">
                    <label class="field-label" for="login-email">Demo account</label>
                    <select class="field-input" id="login-email" name="email" required></select>
                    <label class="field-label" for="login-password">Password</label>
                    <input class="field-input" id="login-password" name="password" type="password" autocomplete="current-password" required>
                    <p class="login-password-note">All premade demo users use the same password: <strong>password</strong></p>
                    <p class="login-error" id="login-error" role="alert" hidden>Those sign-in details do not match.</p>
                    <button class="save-button login-submit" type="submit">Sign in</button>
                </form>
                <button class="login-switch" id="show-registration" type="button">Create your own test account</button>
                <p class="login-local-note">Demo sign-in only. Nothing is sent to a server.</p>
            </div>`;
        elements.root.replaceChildren(screen);
        const accountSelect = document.getElementById('login-email');
        users.forEach((user) => accountSelect.add(new Option(`${user.name} · ${user.role} · ${user.email}`, user.email)));
        document.getElementById('login-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const email = accountSelect.value.toLowerCase();
            const password = document.getElementById('login-password').value;
            const user = users.find((item) => item.email.toLowerCase() === email);
            let isValidPassword = password === DEMO_PASSWORD && PREMADE_USERS.some((item) => item.id === user?.id);
            if (user && !PREMADE_USERS.some((item) => item.id === user.id)) {
                try {
                    isValidPassword = (await hashPassword(password, user.passwordSalt)) === user.passwordHash;
                } catch (error) {
                    isValidPassword = false;
                }
            }
            if (!user || !isValidPassword) {
                document.getElementById('login-error').hidden = false;
                return;
            }
            if (!saveSession(user)) return;
            activeView = 'buildings';
            render();
        });
        document.getElementById('show-registration').addEventListener('click', () => renderLogin('register'));
    }

    function renderBuildings() {
        elements.globalSearch.hidden = true;
        const screen = document.createElement('div');
        screen.className = 'sections-container selection-screen';
        const heading = document.createElement('div');
        heading.className = 'sections-header';
        heading.innerHTML = '<h1>Choose a building</h1><p>Select a building to browse its areas.</p>';
        const grid = document.createElement('div');
        grid.className = 'sections-grid building-grid';
        BUILDINGS.forEach((building) => {
            const button = document.createElement('button');
            button.className = 'section-block';
            button.type = 'button';
            const name = document.createElement('span');
            name.className = 'section-name';
            name.textContent = building.name;
            const info = document.createElement('span');
            info.className = 'section-info';
            info.textContent = `${building.sections.length} Sections · ${roomCountForBuilding(building.id)} Rooms`;
            button.append(name, info);
            button.addEventListener('click', () => {
                activeBuildingId = building.id;
                activeSectionId = null;
                render();
            });
            grid.append(button);
        });
        screen.append(heading, grid);
        addDemoNotice(screen);
        elements.root.replaceChildren(screen);
    }

    function renderSections(building) {
        elements.globalSearch.hidden = true;
        const screen = document.createElement('div');
        screen.className = 'sections-container selection-screen';
        const heading = document.createElement('div');
        heading.className = 'sections-header section-selection-heading';
        const back = document.createElement('button');
        back.className = 'back-btn';
        back.type = 'button';
        back.textContent = '← Buildings';
        back.addEventListener('click', () => {
            activeBuildingId = null;
            render();
        });
        const title = document.createElement('div');
        title.innerHTML = `<p class="selection-kicker">BUILDING</p><h1>${building.name}</h1>`;
        heading.append(back, title);
        const grid = document.createElement('div');
        grid.className = 'sections-grid';
        building.sections.forEach((section) => {
            const button = document.createElement('button');
            button.className = 'section-block';
            button.type = 'button';
            const name = document.createElement('span');
            name.className = 'section-name';
            name.textContent = section.name;
            const info = document.createElement('span');
            info.className = 'section-info';
            info.textContent = `${roomCountForSection(building.id, section.id)} Rooms`;
            button.append(name, info);
            button.addEventListener('click', () => {
                activeSectionId = section.id;
                activeFloor = 0;
                render();
            });
            grid.append(button);
        });
        screen.append(heading, grid);
        addDemoNotice(screen);
        elements.root.replaceChildren(screen);
    }

    function createRoomCard(room) {
        const card = document.createElement('article');
        card.className = `room-card ${room.status}`;
        const main = document.createElement('button');
        main.className = 'room-card-main';
        main.type = 'button';
        main.setAttribute('aria-label', `Edit ${room.name}, ${STATUS_LABELS[room.status]}`);
        const name = document.createElement('span');
        name.className = 'room-name';
        name.textContent = room.name;
        main.append(name);
        if (room.owner) {
            const owner = document.createElement('span');
            owner.className = 'room-owner';
            owner.textContent = `👤 ${room.owner}`;
            main.append(owner);
        }
        const status = document.createElement('span');
        status.className = 'room-status';
        status.textContent = STATUS_LABELS[room.status];
        main.append(status);
        if (room.capacity) {
            const capacity = document.createElement('span');
            capacity.className = 'room-capacity';
            capacity.textContent = `${room.capacity} seats`;
            main.append(capacity);
        }
            const features = Array.isArray(room.features) ? room.features : [];
            if (features.length) {
                const featureList = document.createElement('span');
                featureList.className = 'room-feature-list';
                featureList.textContent = features.slice(0, 3).join(' · ');
                if (features.length > 3) featureList.textContent += ` · +${features.length - 3}`;
                featureList.title = features.join(', ');
                main.append(featureList);
            }
        main.addEventListener('click', () => openRoomDialog(room));
        card.append(main);

        const action = document.createElement('button');
        action.className = 'book-room-button';
        action.type = 'button';
        action.textContent = room.status === 'permanent' ? 'Not bookable' : 'Book';
        action.disabled = room.status === 'permanent';
        action.setAttribute('aria-label', `${room.status === 'permanent' ? 'Not bookable' : 'Book'} ${room.name}`);
        action.addEventListener('click', () => openBookingDialog(room));
        card.append(action);
        return card;
    }

    function renderRooms(building, section) {
        elements.globalSearch.hidden = false;
        const screen = document.createElement('div');
        screen.className = 'rooms-container';
        const header = document.createElement('div');
        header.className = 'rooms-header';
        const back = document.createElement('button');
        back.className = 'back-btn';
        back.type = 'button';
        back.textContent = '← Sections';
        back.addEventListener('click', () => {
            activeSectionId = null;
            render();
        });
        const titles = document.createElement('div');
        titles.className = 'header-titles';
        const buildingName = document.createElement('p');
        buildingName.textContent = building.name;
        const sectionName = document.createElement('h1');
        sectionName.textContent = section.name;
        titles.append(buildingName, sectionName);
        const count = document.createElement('span');
        count.className = 'section-total';
        count.textContent = `${roomCountForSection(building.id, section.id)} rooms`;
        header.append(back, titles, count);

        const layout = document.createElement('div');
        layout.className = 'view-layout';
        const elevator = document.createElement('nav');
        elevator.className = 'elevator';
        elevator.setAttribute('aria-label', 'Choose floor');
        const shaft = document.createElement('div');
        shaft.className = 'elevator-shaft';
        FLOORS.forEach((floor) => {
            const button = document.createElement('button');
            button.className = `floor-btn${activeFloor === floor ? ' active' : ''}`;
            button.type = 'button';
            button.textContent = floorName(floor);
            button.setAttribute('aria-pressed', activeFloor === floor ? 'true' : 'false');
            button.addEventListener('click', () => {
                activeFloor = floor;
                render();
            });
            shaft.append(button);
        });
        elevator.append(shaft);

        const grid = document.createElement('div');
        grid.className = 'rooms-grid';
        const addRoom = document.createElement('button');
        addRoom.className = 'room-card available add-room-card';
        addRoom.type = 'button';
        addRoom.setAttribute('aria-label', 'Add room');
        addRoom.innerHTML = '<span class="add-room-plus">+</span><span class="room-status">Add Room</span>';
        addRoom.addEventListener('click', () => openRoomDialog());
        grid.append(addRoom);

        const query = elements.search.value.trim().toLocaleLowerCase();
        const rooms = data.rooms.filter((room) => room.buildingId === building.id
            && room.sectionId === section.id
            && Number(room.floor) === activeFloor
            && `${room.name} ${room.owner} ${(room.features || []).join(' ')}`.toLocaleLowerCase().includes(query));
        rooms.forEach((room) => grid.append(createRoomCard(room)));
        if (!rooms.length) {
            const empty = document.createElement('div');
            empty.className = 'no-rooms';
            empty.textContent = query ? 'No rooms match your search.' : 'No rooms on this floor.';
            grid.append(empty);
        }

        layout.append(elevator, grid);
        screen.append(header, layout);
        addDemoNotice(screen);
        elements.root.replaceChildren(screen);
    }

    function formatDateTime(value) {
        return new Intl.DateTimeFormat(undefined, {
            weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit'
        }).format(new Date(value));
    }

    function renderBookings() {
        elements.globalSearch.hidden = true;
        const screen = document.createElement('section');
        screen.className = 'bookings-view';
        const header = document.createElement('div');
        header.className = 'bookings-heading';
        const title = document.createElement('div');
        title.innerHTML = `<p class="selection-kicker">SIGNED IN AS ${signedInUser.name.toLocaleUpperCase()}</p><h1>My bookings</h1>`;
        const back = document.createElement('button');
        back.className = 'back-btn';
        back.type = 'button';
        back.textContent = '← Buildings';
        back.addEventListener('click', () => {
            activeView = 'buildings';
            render();
        });
        header.append(title, back);
        const list = document.createElement('div');
        list.className = 'booking-list';
        const bookings = data.bookings
            .filter((booking) => booking.userId === signedInUser.id && !booking.cancelled)
            .sort((first, second) => Date.parse(first.start) - Date.parse(second.start));
        if (!bookings.length) {
            const empty = document.createElement('p');
            empty.className = 'booking-empty';
            empty.textContent = 'You do not have any bookings yet.';
            list.append(empty);
        }
        bookings.forEach((booking) => {
            const room = data.rooms.find((item) => item.id === booking.roomId);
            if (!room) return;
            const building = BUILDINGS.find((item) => item.id === room.buildingId);
            const section = building.sections.find((item) => item.id === room.sectionId);
            const item = document.createElement('article');
            item.className = 'booking-item';
            const details = document.createElement('div');
            details.className = 'booking-item-details';
            const roomName = document.createElement('h2');
            roomName.textContent = room.name;
            const location = document.createElement('p');
            location.textContent = `${building.name} · ${section.name} · Floor ${floorName(room.floor)}`;
            const time = document.createElement('p');
            time.className = 'booking-time';
            time.textContent = `${formatDateTime(booking.start)} – ${new Intl.DateTimeFormat(undefined, { hour: 'numeric', minute: '2-digit' }).format(new Date(booking.end))}`;
            const reason = document.createElement('p');
            reason.className = 'booking-reason';
            reason.textContent = booking.reason || 'used for working';
            details.append(roomName, location, time, reason);
            const actions = document.createElement('div');
            actions.className = 'booking-item-actions';
            const state = document.createElement('span');
            state.className = 'booking-state';
            const now = Date.now();
            state.textContent = Date.parse(booking.end) <= now ? 'Completed'
                : Date.parse(booking.start) <= now ? 'In progress' : 'Upcoming';
            actions.append(state);
            if (Date.parse(booking.end) > now) {
                const cancel = document.createElement('button');
                cancel.className = 'cancel-booking';
                cancel.type = 'button';
                cancel.textContent = 'Cancel booking';
                cancel.addEventListener('click', () => {
                    if (!window.confirm(`Cancel the booking for ${room.name}?`)) return;
                    booking.cancelled = true;
                    if (saveData()) {
                        render();
                        showToast('Booking cancelled');
                    }
                });
                actions.append(cancel);
            }
            item.append(details, actions);
            list.append(item);
        });
        screen.append(header, list);
        addDemoNotice(screen);
        elements.root.replaceChildren(screen);
    }

    function renderAccountNav() {
        elements.accountNav.replaceChildren();
        if (!signedInUser) return;
        const identity = document.createElement('span');
        identity.className = 'signed-in-user';
        identity.textContent = signedInUser.name;
        const bookings = document.createElement('button');
        bookings.className = 'nav-action';
        bookings.type = 'button';
        bookings.textContent = 'My bookings';
        bookings.addEventListener('click', () => {
            activeView = 'bookings';
            render();
        });
        const reset = document.createElement('button');
        reset.className = 'nav-action';
        reset.type = 'button';
        reset.textContent = 'Reset demo';
        reset.addEventListener('click', () => {
            if (!window.confirm('Restore random demo rooms and seeded bookings? Your room and booking changes will be lost.')) return;
            const rooms = createDemoRooms();
            data = { version: 5, rooms, bookings: createDemoBookings(rooms), features: createFeatureCatalog(data.features, rooms) };
            if (!saveData()) return;
            activeBuildingId = null;
            activeSectionId = null;
            activeFloor = 0;
            activeView = 'buildings';
            elements.search.value = '';
            render();
            showToast('Demo data restored');
        });
        const logout = document.createElement('button');
        logout.className = 'nav-action logout-action';
        logout.type = 'button';
        logout.textContent = 'Log off';
        logout.addEventListener('click', () => {
            saveSession(null);
            activeView = 'buildings';
            render();
        });
        elements.accountNav.append(identity, bookings, reset, logout);
    }

    function render() {
        renderAccountNav();
        elements.statusLegend.hidden = !signedInUser;
        if (!signedInUser) {
            renderLogin();
            return;
        }
        const building = BUILDINGS.find((item) => item.id === activeBuildingId);
        const section = building && building.sections.find((item) => item.id === activeSectionId);
        if (activeView === 'bookings') renderBookings();
        else if (!building) renderBuildings();
        else if (!section) renderSections(building);
        else renderRooms(building, section);
    }

    function populateBuildingOptions(selectedBuildingId) {
        elements.roomBuilding.replaceChildren(...BUILDINGS.map((building) => new Option(building.name, building.id)));
        elements.roomBuilding.value = selectedBuildingId;
        populateSectionOptions(selectedBuildingId);
    }

    function populateSectionOptions(buildingId, selectedSectionId) {
        const building = BUILDINGS.find((item) => item.id === buildingId);
        elements.roomSection.replaceChildren(...building.sections.map((section) => new Option(section.name, section.id)));
        elements.roomSection.value = selectedSectionId || building.sections[0].id;
    }

    function renderFeatureOptions(selectedFeatures = []) {
        elements.featureOptions.replaceChildren();
        createFeatureCatalog([...data.features, ...pendingFeatures]).forEach((feature) => {
            const label = document.createElement('label');
            label.className = 'feature-option';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'room-features';
            checkbox.value = feature;
            checkbox.checked = selectedFeatures.includes(feature);
            const name = document.createElement('span');
            name.textContent = feature;
            label.append(checkbox, name);
            elements.featureOptions.append(label);
        });
    }

    function selectedFeatureNames() {
        return Array.from(elements.featureOptions.querySelectorAll('input:checked'), (input) => input.value);
    }

    function renderFeatureSuggestions() {
        const query = elements.otherFeature.value.trim();
        const normalizedQuery = normalizedFeatureName(query);
        elements.featureSuggestions.replaceChildren();
        elements.addFeature.disabled = !normalizedQuery;
        if (!normalizedQuery) {
            elements.featureSuggestions.hidden = true;
            return;
        }

        const suggestions = data.features
            .map((feature) => ({ feature, score: featureSimilarity(query, feature) }))
            .filter(({ feature, score }) => normalizedFeatureName(feature).includes(normalizedQuery)
                || normalizedQuery.includes(normalizedFeatureName(feature)) || score >= 0.38)
            .sort((first, second) => second.score - first.score || first.feature.localeCompare(second.feature))
            .slice(0, 5);

        suggestions.forEach(({ feature }) => {
            const button = document.createElement('button');
            button.className = 'similar-feature';
            button.type = 'button';
            button.setAttribute('role', 'option');
            const name = document.createElement('span');
            name.textContent = feature;
            const note = document.createElement('small');
            note.textContent = normalizedFeatureName(feature) === normalizedQuery ? 'Use existing' : 'Select existing';
            button.append(name, note);
            button.addEventListener('click', () => selectExistingFeature(feature));
            elements.featureSuggestions.append(button);
        });
        elements.featureSuggestions.hidden = suggestions.length === 0;
    }

    function selectExistingFeature(feature) {
        const selected = selectedFeatureNames();
        selected.push(feature);
        pendingFeatures = pendingFeatures.filter((item) => normalizedFeatureName(item) !== normalizedFeatureName(feature));
        renderFeatureOptions(selected);
        elements.otherFeature.value = '';
        renderFeatureSuggestions();
    }

    function addOtherFeature() {
        const name = elements.otherFeature.value.trim().replace(/\s+/g, ' ');
        if (!normalizedFeatureName(name)) return;
        const existing = [...data.features, ...pendingFeatures]
            .find((feature) => normalizedFeatureName(feature) === normalizedFeatureName(name));
        if (existing) {
            selectExistingFeature(existing);
            return;
        }
        const selected = selectedFeatureNames();
        pendingFeatures.push(name);
        selected.push(name);
        renderFeatureOptions(selected);
        elements.otherFeature.value = '';
        renderFeatureSuggestions();
    }

    function openRoomDialog(room) {
        elements.roomForm.reset();
        pendingFeatures = [];
        elements.roomId.value = room ? room.id : '';
        const buildingId = room ? room.buildingId : activeBuildingId || BUILDINGS[0].id;
        populateBuildingOptions(buildingId);
        populateSectionOptions(buildingId, room ? room.sectionId : activeSectionId);
        elements.roomFloor.replaceChildren(...[0, 1, 2, 3, 4].map((floor) => new Option(`Floor ${floorName(floor)}`, String(floor))));
        elements.roomName.value = room ? room.name : '';
        elements.roomFloor.value = String(room ? room.floor : activeFloor);
        elements.roomStatus.value = room ? room.status : 'available';
        elements.roomCapacity.value = room ? room.capacity || '' : '';
        elements.roomOwner.value = room ? room.owner || '' : '';
        renderFeatureOptions(room && Array.isArray(room.features) ? room.features : []);
        renderFeatureSuggestions();
        elements.roomDialogTitle.textContent = room ? 'Edit Room' : 'Add Room';
        elements.deleteRoom.hidden = !room;
        elements.roomDialog.showModal();
        elements.roomName.focus();
    }

    function localDateTimeValue(date) {
        return new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    }

    function openBookingDialog(room) {
        if (room.status === 'permanent') return;
        elements.bookingForm.reset();
        elements.bookingForm.dataset.roomId = room.id;
        elements.bookingTitle.textContent = `Book ${room.name}`;
        elements.bookingRoomInfo.textContent = `${BUILDINGS.find((item) => item.id === room.buildingId).name} · ${room.capacity || 'No'} seat capacity`;
        const start = dateTimeOffset(1, 9);
        elements.bookingStart.value = localDateTimeValue(start);
        elements.bookingEnd.value = localDateTimeValue(new Date(start.getTime() + 60 * 60 * 1000));
        elements.bookingError.hidden = true;
        elements.bookingDialog.showModal();
    }

    function closeDialog(dialog) {
        dialog.close();
    }

    function showToast(message) {
        elements.toast.textContent = message;
        elements.toast.classList.add('visible');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => elements.toast.classList.remove('visible'), 2600);
    }

    elements.roomBuilding.addEventListener('change', () => populateSectionOptions(elements.roomBuilding.value));
    elements.search.addEventListener('input', render);
    elements.otherFeature.addEventListener('input', renderFeatureSuggestions);
    elements.addFeature.addEventListener('click', addOtherFeature);
    elements.otherFeature.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            addOtherFeature();
        }
    });
    document.getElementById('close-dialog').addEventListener('click', () => closeDialog(elements.roomDialog));
    document.getElementById('cancel-dialog').addEventListener('click', () => closeDialog(elements.roomDialog));
    document.getElementById('close-booking-dialog').addEventListener('click', () => closeDialog(elements.bookingDialog));
    document.getElementById('cancel-booking-dialog').addEventListener('click', () => closeDialog(elements.bookingDialog));
    [elements.roomDialog, elements.bookingDialog].forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) closeDialog(dialog);
        });
    });

    elements.roomForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const id = elements.roomId.value;
        const room = {
            id: id || (crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random()}`),
            buildingId: elements.roomBuilding.value,
            sectionId: elements.roomSection.value,
            floor: Number(elements.roomFloor.value),
            name: elements.roomName.value.trim(),
            owner: elements.roomOwner.value.trim(),
            capacity: elements.roomCapacity.value ? Number(elements.roomCapacity.value) : null,
            features: Array.from(elements.featureOptions.querySelectorAll('input:checked'), (input) => input.value),
            status: elements.roomStatus.value
        };
        data.features = createFeatureCatalog([...data.features, ...pendingFeatures]);
        data.version = 5;
        const existingIndex = data.rooms.findIndex((item) => item.id === id);
        if (existingIndex === -1) data.rooms.push(room);
        else data.rooms[existingIndex] = room;
        if (!saveData()) return;
        activeBuildingId = room.buildingId;
        activeSectionId = room.sectionId;
        activeFloor = room.floor;
        closeDialog(elements.roomDialog);
        render();
        showToast(existingIndex === -1 ? 'Room added' : 'Room updated');
    });

    elements.deleteRoom.addEventListener('click', () => {
        const id = elements.roomId.value;
        const room = data.rooms.find((item) => item.id === id);
        if (!room || !window.confirm(`Delete ${room.name}?`)) return;
        data.rooms = data.rooms.filter((item) => item.id !== id);
        data.bookings = data.bookings.filter((booking) => booking.roomId !== id);
        if (!saveData()) return;
        closeDialog(elements.roomDialog);
        render();
        showToast('Room deleted');
    });

    elements.bookingForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const room = data.rooms.find((item) => item.id === elements.bookingForm.dataset.roomId);
        const start = new Date(elements.bookingStart.value);
        const end = new Date(elements.bookingEnd.value);
        const now = new Date();
        if (!room || Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || start < now || end <= start) {
            elements.bookingError.textContent = 'Choose a future start time and an end time after it.';
            elements.bookingError.hidden = false;
            return;
        }
        if (room.status === 'permanent') {
            elements.bookingError.textContent = 'Permanent rooms are not bookable.';
            elements.bookingError.hidden = false;
            return;
        }
        const overlapping = data.bookings.filter((booking) => !booking.cancelled
            && Date.parse(booking.start) < end.getTime()
            && Date.parse(booking.end) > start.getTime());
        if (room.capacity && overlapping.filter((booking) => booking.roomId === room.id).length >= room.capacity) {
            elements.bookingError.textContent = 'This room is at capacity for the selected time.';
            elements.bookingError.hidden = false;
            return;
        }
        const personalConflict = overlapping.some((booking) => booking.userId === signedInUser.id);
        if (personalConflict && !window.confirm('You already have a booking during this time. Continue anyway?')) return;
        data.bookings.push({
            id: crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random()}`,
            roomId: room.id,
            userId: signedInUser.id,
            start: start.toISOString(),
            end: end.toISOString(),
            reason: elements.bookingReason.value.trim() || 'used for working',
            cancelled: false,
            demo: false
        });
        if (!saveData()) return;
        closeDialog(elements.bookingDialog);
        activeView = 'bookings';
        render();
        showToast('Room booked successfully');
    });

    window.addEventListener('storage', (event) => {
        if (event.key === STORAGE_KEY || event.key === SESSION_KEY) {
            data = loadData();
            signedInUser = loadSession();
            render();
        }
    });

    render();
}());
