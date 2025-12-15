// Theatre Seating Management - Adapted for booking.php
class TheatreSeatingManager {
    constructor(ticketCategories, selectedTickets, updateCheckoutCallback) {
        this.ticketCategories = ticketCategories || [];
        this.selectedTickets = selectedTickets || {};
        this.updateCheckoutCallback = updateCheckoutCallback;
        this.seats = [];
        this.container = null;
        this.init();
    }

    init() {
        this.container = document.getElementById('theatre-seats');
        if (!this.container) {
            console.error('TheatreSeatingManager: Container not found!');
            return;
        }
        
        if (!this.ticketCategories || this.ticketCategories.length === 0) {
            console.error('TheatreSeatingManager: No ticket categories provided!');
            return;
        }
        
        this.generateSeats();
        this.renderSeating();
    }

    generateSeats() {
        const rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        const seatsPerRow = 12;
        
        // Get booked seats from reservations (if any)
        const bookedSeats = new Set(); // Will be populated from API if needed

        // Map ticket categories to seat categories
        const categoryMap = {};
        this.ticketCategories.forEach((cat, index) => {
            if (index === 0) categoryMap['vip'] = cat;
            else if (index === 1) categoryMap['gold'] = cat;
            else categoryMap['regular'] = cat;
        });

        rows.forEach((row, rowIndex) => {
            let category, price, categoryName;

            // Determine category based on row
            // Theatre: vip → Gold, premium → Premium, regular → Regular
            // Use actual category names from ticketCategories array (sorted by price DESC)
            if (rowIndex <= 2) {
                category = 'vip'; // Maps to highest price category (Gold)
                // Get the first category (highest price) - should be Gold
                const gold = this.ticketCategories.find(cat => cat.category_name === 'Gold') || this.ticketCategories[0];
                categoryName = gold?.category_name || 'Gold';
                price = parseFloat(gold?.price || 0);
            } else if (rowIndex <= 5) {
                category = 'premium'; // Maps to middle price category (Premium)
                // Get the second category or Premium
                const premium = this.ticketCategories.find(cat => cat.category_name === 'Premium') || 
                               (this.ticketCategories.length > 1 ? this.ticketCategories[1] : this.ticketCategories[0]);
                categoryName = premium?.category_name || 'Premium';
                price = parseFloat(premium?.price || 0);
            } else {
                category = 'regular'; // Maps to lowest price category (Regular)
                // Get the third category or Regular
                const regular = this.ticketCategories.find(cat => cat.category_name === 'Regular') || 
                               (this.ticketCategories.length > 2 ? this.ticketCategories[2] : 
                                this.ticketCategories.length > 1 ? this.ticketCategories[1] : this.ticketCategories[0]);
                categoryName = regular?.category_name || 'Regular';
                price = parseFloat(regular?.price || 0);
            }
            
            // Ensure we have ticket categories
            if (!this.ticketCategories || this.ticketCategories.length === 0) {
                console.error('No ticket categories available!');
                return; // Can't generate seats without categories
            }
            
            // Validate category exists, but use fallback if needed
            const categoryExists = this.ticketCategories.find(c => c.category_name === categoryName);
            if (!categoryExists) {
                console.warn('Category not found in database:', categoryName, 'Available:', this.ticketCategories.map(c => c.category_name));
                // Use the first available category as fallback
                if (this.ticketCategories.length > 0) {
                    const fallback = this.ticketCategories[0];
                    categoryName = fallback.category_name;
                    price = parseFloat(fallback.price || 0);
                } else {
                    return; // Skip this row
                }
            }

            for (let number = 1; number <= seatsPerRow; number++) {
                const seatKey = `${row}-${number}`;
                this.seats.push({
                    id: seatKey,
                    row: row,
                    number: number,
                    category: category,
                    categoryName: categoryName,
                    status: bookedSeats.has(seatKey) ? 'booked' : 'available',
                    price: price
                });
            }
        });
    }

    renderSeating() {
        if (!this.container) {
            console.error('Theatre container not found!');
            return;
        }
        
        if (this.seats.length === 0) {
            console.error('ERROR: No seats were generated! Check ticketCategories and category matching.');
            return;
        }
        
        this.container.innerHTML = '';
        const rows = [...new Set(this.seats.map(s => s.row))];

        rows.forEach(rowLetter => {
            const rowSeats = this.seats.filter(s => s.row === rowLetter);
            
            const rowDiv = document.createElement('div');
            rowDiv.className = 'theatre-row';

            // Left row label
            const leftLabel = document.createElement('div');
            leftLabel.className = 'row-label';
            leftLabel.textContent = rowLetter;
            rowDiv.appendChild(leftLabel);

            // Seats container
            const seatsContainer = document.createElement('div');
            seatsContainer.className = 'row-seats';

            rowSeats.forEach((seat, index) => {
                const button = this.createSeatButton(seat);
                seatsContainer.appendChild(button);

                // Add aisle gap after 6th seat
                if (index === 5) {
                    const aisle = document.createElement('div');
                    aisle.className = 'aisle-gap';
                    seatsContainer.appendChild(aisle);
                }
            });

            rowDiv.appendChild(seatsContainer);

            // Right row label
            const rightLabel = document.createElement('div');
            rightLabel.className = 'row-label';
            rightLabel.textContent = rowLetter;
            rowDiv.appendChild(rightLabel);

            this.container.appendChild(rowDiv);
        });
    }

    createSeatButton(seat) {
        const button = document.createElement('button');
        button.className = `seat-btn ${seat.status} ${seat.category}`;
        button.title = `Seat ${seat.row}${seat.number} - ${seat.categoryName} - $${seat.price.toFixed(2)}`;
        button.disabled = seat.status === 'booked';
        button.setAttribute('data-seat-id', seat.id);
        button.setAttribute('data-category', seat.categoryName);
        button.setAttribute('data-price', seat.price);
        
        button.addEventListener('click', () => this.toggleSeat(seat.id));
        
        return button;
    }

    toggleSeat(seatId) {
        const seat = this.seats.find(s => s.id === seatId);
        if (!seat || seat.status === 'booked') return;

        if (seat.status === 'available') {
            seat.status = 'selected';
            // Update selectedTickets count for this category
            if (!this.selectedTickets[seat.categoryName]) {
                this.selectedTickets[seat.categoryName] = 0;
            }
            this.selectedTickets[seat.categoryName]++;
        } else {
            seat.status = 'available';
            // Decrease selectedTickets count
            if (this.selectedTickets[seat.categoryName] > 0) {
                this.selectedTickets[seat.categoryName]--;
            }
        }

        this.updateSeatButton(seatId);
        if (this.updateCheckoutCallback) {
            this.updateCheckoutCallback();
        }
    }

    updateSeatButton(seatId) {
        const button = document.querySelector(`button[data-seat-id="${seatId}"]`);
        if (!button) return;

        const seat = this.seats.find(s => s.id === seatId);
        button.className = `seat-btn ${seat.status} ${seat.category}`;
    }

    clearSelection() {
        this.seats.forEach(seat => {
            if (seat.status === 'selected') {
                seat.status = 'available';
                this.updateSeatButton(seat.id);
                // Reset selectedTickets
                if (this.selectedTickets[seat.categoryName] > 0) {
                    this.selectedTickets[seat.categoryName] = 0;
                }
            }
        });
        if (this.updateCheckoutCallback) {
            this.updateCheckoutCallback();
        }
    }

    // Update seat availability based on reservations
    updateAvailability(reservations) {
        // Implementation for updating seat availability from reservations
        // This can be called when reservations are loaded
    }
}

// Export for use in booking.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TheatreSeatingManager;
}

