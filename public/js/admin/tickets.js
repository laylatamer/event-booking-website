// Tickets Section Scripts

let tickets = [
    { id: 1, event: 'Summer Music Festival', type: 'General Admission', price: 60.00, available: 124, sold: 376 },
    { id: 2, event: 'Tech Conference 2023', type: 'Standard Pass', price: 75.00, available: 56, sold: 244 },
    { id: 3, event: 'Art Exhibition', type: 'Adult Ticket', price: 15.00, available: 42, sold: 158 },
    { id: 4, event: 'Food Festival', type: 'Tasting Pass', price: 15.00, available: 28, sold: 172 }
];

let currentPage = { tickets: 1 };
const itemsPerPage = 4;

document.addEventListener('DOMContentLoaded', function() {
    initializeTicketsEventListeners();
    loadTickets();
});

function initializeTicketsEventListeners() {
    const addTicketBtn = document.getElementById('add-ticket-btn');
    if (addTicketBtn) {
        addTicketBtn.addEventListener('click', () => openModal('add-ticket'));
    }
    
    const addTicketForm = document.getElementById('add-ticket-form');
    if (addTicketForm) {
        addTicketForm.addEventListener('submit', handleAddTicket);
    }
    
    const editTicketForm = document.getElementById('edit-ticket-form');
    if (editTicketForm) {
        editTicketForm.addEventListener('submit', handleEditTicket);
    }
    
    const ticketsPrev = document.getElementById('tickets-prev');
    const ticketsNext = document.getElementById('tickets-next');
    if (ticketsPrev) ticketsPrev.addEventListener('click', () => changePage('tickets', -1));
    if (ticketsNext) ticketsNext.addEventListener('click', () => changePage('tickets', 1));
}

function loadTickets() {
    const ticketsTableBody = document.getElementById('tickets-table-body');
    if (!ticketsTableBody) return;
    
    ticketsTableBody.innerHTML = '';
    
    if (tickets.length === 0) {
        ticketsTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="empty-state">
                    <i data-feather="ticket"></i>
                    <p>No tickets found</p>
                </td>
            </tr>
        `;
        feather.replace();
        return;
    }

    const startIndex = (currentPage.tickets - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const ticketsToShow = tickets.slice(startIndex, endIndex);
    
    ticketsToShow.forEach(ticket => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${ticket.event}</td>
            <td>${ticket.type}</td>
            <td>$${ticket.price.toFixed(2)}</td>
            <td>${ticket.available}</td>
            <td>${ticket.sold}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn edit-ticket" data-id="${ticket.id}">
                        <i data-feather="edit-2"></i>
                    </button>
                    <button class="action-btn delete delete-ticket" data-id="${ticket.id}">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </td>
        `;
        ticketsTableBody.appendChild(row);
    });
    
    document.getElementById('tickets-start').textContent = startIndex + 1;
    document.getElementById('tickets-end').textContent = Math.min(endIndex, tickets.length);
    document.getElementById('tickets-total').textContent = tickets.length;
    
    updatePaginationButtons('tickets', tickets.length, itemsPerPage);
    
    feather.replace();
    document.querySelectorAll('.edit-ticket').forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = parseInt(this.getAttribute('data-id'));
            editTicket(ticketId);
        });
    });
    
    document.querySelectorAll('.delete-ticket').forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = parseInt(this.getAttribute('data-id'));
            deleteTicket(ticketId);
        });
    });
}

function handleAddTicket(e) {
    e.preventDefault();
    
    const event = document.getElementById('ticket-event').value;
    const type = document.getElementById('ticket-type').value;
    const price = parseFloat(document.getElementById('ticket-price').value);
    const quantity = parseInt(document.getElementById('ticket-quantity').value);
    const description = document.getElementById('ticket-description').value;
    
    const newTicket = {
        id: tickets.length > 0 ? Math.max(...tickets.map(t => t.id)) + 1 : 1,
        event: document.querySelector(`#ticket-event option[value="${event}"]`).textContent,
        type,
        price,
        available: quantity,
        sold: 0,
        description
    };
    
    tickets.push(newTicket);
    loadTickets();
    closeModal('add-ticket');
    alert('Ticket type added successfully!');
}

function editTicket(ticketId) {
    const ticket = tickets.find(t => t.id === ticketId);
    if (!ticket) return;
    
    document.getElementById('edit-ticket-id').value = ticket.id;
    document.getElementById('edit-ticket-event').value = ticket.event.toLowerCase().replace(' ', '-');
    document.getElementById('edit-ticket-type').value = ticket.type;
    document.getElementById('edit-ticket-price').value = ticket.price;
    document.getElementById('edit-ticket-quantity').value = ticket.available;
    document.getElementById('edit-ticket-description').value = ticket.description || '';
    
    openModal('edit-ticket');
}

function handleEditTicket(e) {
    e.preventDefault();
    
    const ticketId = parseInt(document.getElementById('edit-ticket-id').value);
    const event = document.getElementById('edit-ticket-event').value;
    const type = document.getElementById('edit-ticket-type').value;
    const price = parseFloat(document.getElementById('edit-ticket-price').value);
    const quantity = parseInt(document.getElementById('edit-ticket-quantity').value);
    const description = document.getElementById('edit-ticket-description').value;
    
    const ticketIndex = tickets.findIndex(t => t.id === ticketId);
    if (ticketIndex !== -1) {
        tickets[ticketIndex].event = document.querySelector(`#edit-ticket-event option[value="${event}"]`).textContent;
        tickets[ticketIndex].type = type;
        tickets[ticketIndex].price = price;
        tickets[ticketIndex].available = quantity;
        tickets[ticketIndex].description = description;
        
        loadTickets();
        closeModal('edit-ticket');
        alert('Ticket type updated successfully!');
    }
}

function deleteTicket(ticketId) {
    showConfirmation('Are you sure you want to delete this ticket type?', () => {
        const ticketIndex = tickets.findIndex(t => t.id === ticketId);
        if (ticketIndex !== -1) {
            tickets.splice(ticketIndex, 1);
            loadTickets();
            alert('Ticket type deleted successfully!');
        }
    });
}

function changePage(section, direction) {
    const totalItems = tickets.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    currentPage[section] += direction;
    
    if (currentPage[section] < 1) {
        currentPage[section] = 1;
    } else if (currentPage[section] > totalPages) {
        currentPage[section] = totalPages;
    }
    
    loadTickets();
}

