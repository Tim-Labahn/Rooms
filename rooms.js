(function () {
    const STORAGE_KEY = 'timlabahn.rooms.directory.v2';
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
        dialog: document.getElementById('room-dialog'),
        form: document.getElementById('room-form'),
        dialogTitle: document.getElementById('dialog-title'),
        roomId: document.getElementById('room-id'),
        roomName: document.getElementById('room-name'),
        roomBuilding: document.getElementById('room-building'),
        roomSection: document.getElementById('room-section'),
        roomFloor: document.getElementById('room-floor'),
        roomStatus: document.getElementById('room-status'),
        roomCapacity: document.getElementById('room-capacity'),
        roomOwner: document.getElementById('room-owner'),
        deleteRoom: document.getElementById('delete-room'),
        toast: document.getElementById('toast')
    };

    let data = loadData();
    let activeBuildingId = null;
    let activeSectionId = null;
    let activeFloor = 0;
    let toastTimer;

    function createDemoRooms() {
        const rooms = [];
        BUILDINGS.forEach((building) => {
            building.sections.forEach((section, sectionIndex) => {
                for (let floor = 0; floor <= 4; floor += 1) {
                    for (let number = 1; number <= 10; number += 1) {
                        const roomNumber = `${floor === 0 ? 'E' : floor}${String(number).padStart(2, '0')}`;
                        const status = number === 10 ? 'full'
                            : number === 8 ? 'occupied'
                                : number === 6 ? 'permanent' : 'available';
                        rooms.push({
                            id: `demo-${building.id}-${section.id}-${floor}-${number}`,
                            buildingId: building.id,
                            sectionId: section.id,
                            floor,
                            name: `${ROOM_TYPES[(number + sectionIndex + floor) % ROOM_TYPES.length]} ${roomNumber}`,
                            owner: number % 5 === 0 ? `${OWNER_TEAMS[(number + sectionIndex) % OWNER_TEAMS.length]} Team` : '',
                            capacity: 4 + ((floor * 3 + sectionIndex * 4 + number * 2) % 17),
                            status
                        });
                    }
                }
            });
        });
        return rooms;
    }

    function isValidRoom(room) {
        const building = BUILDINGS.find((item) => item.id === room.buildingId);
        return room && typeof room.id === 'string' && typeof room.name === 'string'
            && building && building.sections.some((section) => section.id === room.sectionId)
            && FLOORS.includes(Number(room.floor)) && STATUSES.includes(room.status);
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
            if (saved && saved.version === 2 && Array.isArray(saved.rooms)) {
                return { version: 2, rooms: saved.rooms.filter(isValidRoom) };
            }
        } catch (error) {
            console.warn('Could not read saved room data.', error);
        }
        const initialData = { version: 2, rooms: createDemoRooms() };
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(initialData));
        } catch (error) {
            console.warn('Could not save demo rooms.', error);
        }
        return initialData;
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
        elements.root.replaceChildren(screen);
    }

    function createRoomCard(room) {
        const button = document.createElement('button');
        button.className = `room-card ${room.status}`;
        button.type = 'button';
        button.setAttribute('aria-label', `Edit ${room.name}, ${STATUS_LABELS[room.status]}`);
        const name = document.createElement('span');
        name.className = 'room-name';
        name.textContent = room.name;
        if (room.owner) {
            const owner = document.createElement('span');
            owner.className = 'room-owner';
            owner.textContent = `👤 ${room.owner}`;
            button.append(name, owner);
        } else {
            button.append(name);
        }
        const details = document.createElement('span');
        details.className = 'room-card-details';
        const status = document.createElement('span');
        status.className = 'room-status';
        status.textContent = STATUS_LABELS[room.status];
        details.append(status);
        if (room.capacity) {
            const capacity = document.createElement('span');
            capacity.className = 'room-capacity';
            capacity.textContent = `${room.capacity} seats`;
            details.append(capacity);
        }
        button.append(details);
        button.addEventListener('click', () => openDialog(room));
        return button;
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
        addRoom.addEventListener('click', () => openDialog());
        grid.append(addRoom);

        const query = elements.search.value.trim().toLocaleLowerCase();
        const rooms = data.rooms.filter((room) => room.buildingId === building.id
            && room.sectionId === section.id
            && Number(room.floor) === activeFloor
            && `${room.name} ${room.owner}`.toLocaleLowerCase().includes(query));
        rooms.forEach((room) => grid.append(createRoomCard(room)));
        if (!rooms.length) {
            const empty = document.createElement('div');
            empty.className = 'no-rooms';
            empty.textContent = query ? 'No rooms match your search.' : 'No rooms on this floor.';
            grid.append(empty);
        }

        layout.append(elevator, grid);
        screen.append(header, layout);
        elements.root.replaceChildren(screen);
    }

    function render() {
        const building = BUILDINGS.find((item) => item.id === activeBuildingId);
        const section = building && building.sections.find((item) => item.id === activeSectionId);
        if (!building) renderBuildings();
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

    function openDialog(room) {
        elements.form.reset();
        elements.roomId.value = room ? room.id : '';
        populateBuildingOptions(room ? room.buildingId : activeBuildingId || BUILDINGS[0].id);
        populateSectionOptions(room ? room.buildingId : activeBuildingId || BUILDINGS[0].id, room ? room.sectionId : activeSectionId);
        elements.roomFloor.replaceChildren(...[0, 1, 2, 3, 4].map((floor) => new Option(`Floor ${floorName(floor)}`, String(floor))));
        elements.roomName.value = room ? room.name : '';
        elements.roomFloor.value = String(room ? room.floor : activeFloor);
        elements.roomStatus.value = room ? room.status : 'available';
        elements.roomCapacity.value = room ? room.capacity || '' : '';
        elements.roomOwner.value = room ? room.owner || '' : '';
        elements.dialogTitle.textContent = room ? 'Edit Room' : 'Add Room';
        elements.deleteRoom.hidden = !room;
        elements.dialog.showModal();
        elements.roomName.focus();
    }

    function closeDialog() {
        elements.dialog.close();
    }

    function showToast(message) {
        elements.toast.textContent = message;
        elements.toast.classList.add('visible');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => elements.toast.classList.remove('visible'), 2600);
    }

    document.getElementById('close-dialog').addEventListener('click', closeDialog);
    document.getElementById('cancel-dialog').addEventListener('click', closeDialog);
    elements.dialog.addEventListener('click', (event) => {
        if (event.target === elements.dialog) closeDialog();
    });
    elements.roomBuilding.addEventListener('change', () => populateSectionOptions(elements.roomBuilding.value));
    elements.search.addEventListener('input', render);

    elements.form.addEventListener('submit', (event) => {
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
            status: elements.roomStatus.value
        };
        const existingIndex = data.rooms.findIndex((item) => item.id === id);
        if (existingIndex === -1) data.rooms.push(room);
        else data.rooms[existingIndex] = room;
        if (!saveData()) return;
        activeBuildingId = room.buildingId;
        activeSectionId = room.sectionId;
        activeFloor = room.floor;
        closeDialog();
        render();
        showToast(existingIndex === -1 ? 'Room added' : 'Room updated');
    });

    elements.deleteRoom.addEventListener('click', () => {
        const id = elements.roomId.value;
        const room = data.rooms.find((item) => item.id === id);
        if (!room || !window.confirm(`Delete ${room.name}?`)) return;
        data.rooms = data.rooms.filter((item) => item.id !== id);
        if (!saveData()) return;
        closeDialog();
        render();
        showToast('Room deleted');
    });

    document.getElementById('reset-data').addEventListener('click', () => {
        if (!window.confirm('Restore the original demo rooms and discard your local changes?')) return;
        data = { version: 2, rooms: createDemoRooms() };
        if (!saveData()) return;
        activeBuildingId = null;
        activeSectionId = null;
        activeFloor = 0;
        elements.search.value = '';
        render();
        showToast('Demo data restored');
    });

    window.addEventListener('storage', (event) => {
        if (event.key === STORAGE_KEY) {
            data = loadData();
            render();
        }
    });

    render();
}());