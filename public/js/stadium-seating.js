// Stadium Seating Management - Adapted for booking.php
class StadiumSeatingManager {
    constructor(ticketCategories, selectedTickets, updateCheckoutCallback) {
        this.ticketCategories = ticketCategories || [];
        this.selectedTickets = selectedTickets || {};
        this.updateCheckoutCallback = updateCheckoutCallback;
        this.seats = [];
        this.init();
    }

    init() {
        if (!this.ticketCategories || this.ticketCategories.length === 0) {
            console.error('StadiumSeatingManager: No ticket categories provided!');
            return;
        }
        
        this.generateSeats();
        this.renderSeating();
    }

    generateSeats() {
        const sections = ['North', 'South', 'East', 'West'];
        const bookedSeats = new Set(); // Will be populated from API if needed

        sections.forEach(section => {
            const rows = (section === 'North' || section === 'South') ? 8 : 12;
            const seatsPerRow = (section === 'North' || section === 'South') ? 20 : 10;

            for (let rowNum = 1; rowNum <= rows; rowNum++) {
                let category, price, categoryName;

                // Map rows to ticket categories
                // Stadium: vip → Cat1, premium → Cat2, regular → Cat3
                // Use actual category names from ticketCategories array (sorted by price DESC)
                if (rowNum <= 2) {
                    category = 'vip'; // Maps to highest price category (Cat1)
                    // Get the first category (highest price) - should be Cat1
                    const cat1 = this.ticketCategories.find(cat => cat.category_name === 'Cat1') || this.ticketCategories[0];
                    categoryName = cat1?.category_name || 'Cat1';
                    price = parseFloat(cat1?.price || 0);
                } else if (rowNum <= 5) {
                    category = 'premium'; // Maps to middle price category (Cat2)
                    // Get the second category or Cat2
                    const cat2 = this.ticketCategories.find(cat => cat.category_name === 'Cat2') || 
                                (this.ticketCategories.length > 1 ? this.ticketCategories[1] : this.ticketCategories[0]);
                    categoryName = cat2?.category_name || 'Cat2';
                    price = parseFloat(cat2?.price || 0);
                } else {
                    category = 'regular'; // Maps to lowest price category (Cat3)
                    // Get the third category or Cat3
                    const cat3 = this.ticketCategories.find(cat => cat.category_name === 'Cat3') || 
                                (this.ticketCategories.length > 2 ? this.ticketCategories[2] : 
                                 this.ticketCategories.length > 1 ? this.ticketCategories[1] : this.ticketCategories[0]);
                    categoryName = cat3?.category_name || 'Cat3';
                    price = parseFloat(cat3?.price || 0);
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
                        continue;
                    }
                }

                for (let seatNum = 1; seatNum <= seatsPerRow; seatNum++) {
                    const seatId = `${section}-${rowNum}-${seatNum}`;
                    this.seats.push({
                        id: seatId,
                        section: section,
                        row: rowNum,
                        number: seatNum,
                        category: category,
                        categoryName: categoryName,
                        status: bookedSeats.has(seatId) ? 'booked' : 'available',
                        price: price
                    });
                }
            }
        });
    }

    renderSeating() {
        const sections = ['North', 'South', 'West', 'East'];
        
        if (this.seats.length === 0) {
            console.error('ERROR: No seats were generated! Check ticketCategories and category matching.');
            return;
        }
        
        sections.forEach(section => {
            const container = document.getElementById(`${section.toLowerCase()}-seats`);
            if (!container) {
                console.error(`Container not found for ${section}: ${section.toLowerCase()}-seats`);
                console.log('Available IDs:', Array.from(document.querySelectorAll('[id*="seats"]')).map(el => el.id));
                return;
            }
            
            container.innerHTML = '';
            const sectionSeats = this.seats.filter(s => s.section === section);
            const rows = [...new Set(sectionSeats.map(s => s.row))].sort((a, b) => a - b);
            
            const isVertical = section === 'West' || section === 'East';
            const displayRows = isVertical ? rows : (section === 'South' ? rows.reverse() : rows);

            displayRows.forEach(rowNum => {
                const rowSeats = sectionSeats.filter(s => s.row === rowNum);
                const rowDiv = document.createElement('div');
                rowDiv.className = 'seat-row';

                rowSeats.forEach((seat, index) => {
                    const seatWrapper = document.createElement('div');
                    seatWrapper.className = 'seat-wrapper';
                    
                    // Calculate curve effect for stadium layout
                    const totalSeats = rowSeats.length;
                    const middle = totalSeats / 2;
                    const distanceFromMiddle = Math.abs(index - middle);
                    const curve = Math.floor(distanceFromMiddle / 3) * 2;

                    if (!isVertical && section === 'North') {
                        seatWrapper.style.marginTop = `${curve}px`;
                    } else if (!isVertical && section === 'South') {
                        seatWrapper.style.marginBottom = `${curve}px`;
                    } else if (isVertical && section === 'West') {
                        seatWrapper.style.marginLeft = `${curve}px`;
                    } else if (isVertical && section === 'East') {
                        seatWrapper.style.marginRight = `${curve}px`;
                    }

                    const button = this.createSeatButton(seat);
                    seatWrapper.appendChild(button);
                    rowDiv.appendChild(seatWrapper);
                });

                container.appendChild(rowDiv);
            });
        });
    }

    createSeatButton(seat) {
        const button = document.createElement('button');
        button.className = `seat-btn compact ${seat.status} ${seat.category}`;
        button.title = `${seat.section} - Row ${seat.row} Seat ${seat.number} - ${seat.categoryName} - $${seat.price.toFixed(2)}`;
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
        button.className = `seat-btn compact ${seat.status} ${seat.category}`;
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
    }
}

// Export for use in booking.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StadiumSeatingManager;
}

