import React, { useState } from 'react';
import logo from 'figma:asset/794c0c45a7c9b82ebff6973913e0ba43a4728f5f.png';
import './EventTicket.css';

interface EventTicketProps {
  eventName: string;
  eventDate: string;
  userName: string;
  eventTime: string;
  venue: string;
}

export function EventTicket({ eventName, eventDate, userName, eventTime, venue }: EventTicketProps) {
  const [isFlipped, setIsFlipped] = useState(false);

  const handleFlip = () => {
    setIsFlipped(!isFlipped);
  };

  return (
    <div className="flex flex-col items-center gap-8 p-8">
      <div className="perspective-container">
        <div className={`ticket-container ${isFlipped ? 'flipped' : ''}`}>
          {/* Front of Ticket */}
          <div className="ticket-face ticket-front">
            <div className="ticket-notch-left"></div>
            <div className="ticket-notch-right"></div>
            
            <div className="ticket-main-section">
              <div className="ticket-border">
                <div className="ticket-content-front">
                  <h1 className="ticket-title">TICKET</h1>
                  
                  <div className="ticket-logo-container">
                    <img src={logo} alt="EGZLY Logo" className="ticket-logo" />
                  </div>

                  <div className="ticket-info-grid">
                    <div className="ticket-info-item">
                      <div className="ticket-info-label">DATE</div>
                      <div className="ticket-info-value">{eventDate}</div>
                    </div>
                    <div className="ticket-separator"></div>
                    <div className="ticket-info-item">
                      <div className="ticket-info-label">NAME</div>
                      <div className="ticket-info-value">{userName}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="ticket-stub">
              <div className="ticket-stub-text">{eventName}</div>
              <div className="ticket-barcode">
                {[...Array(20)].map((_, i) => (
                  <div key={i} className="barcode-line" style={{ height: `${Math.random() * 60 + 40}%` }}></div>
                ))}
              </div>
              <div className="ticket-stub-number">760987-28875</div>
            </div>
          </div>

          {/* Back of Ticket */}
          <div className="ticket-face ticket-back">
            <div className="ticket-notch-left"></div>
            <div className="ticket-notch-right"></div>
            
            <div className="ticket-back-content">
              <h2 className="ticket-back-title">{eventName}</h2>
              
              <div className="ticket-back-details">
                <div className="ticket-detail-row">
                  <span className="ticket-detail-label">Date:</span>
                  <span className="ticket-detail-value">{eventDate}</span>
                </div>
                <div className="ticket-detail-row">
                  <span className="ticket-detail-label">Time:</span>
                  <span className="ticket-detail-value">{eventTime}</span>
                </div>
                <div className="ticket-detail-row">
                  <span className="ticket-detail-label">Venue:</span>
                  <span className="ticket-detail-value">{venue}</span>
                </div>
                <div className="ticket-detail-row">
                  <span className="ticket-detail-label">Guest:</span>
                  <span className="ticket-detail-value">{userName}</span>
                </div>
              </div>

              <div className="ticket-back-footer">
                <p>Please present this ticket at the entrance</p>
                <p className="ticket-back-code">Ticket ID: TKT-{Math.random().toString(36).substr(2, 9).toUpperCase()}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <button onClick={handleFlip} className="flip-button">
        <span className="flip-button-icon">↻</span>
        <span>{isFlipped ? 'View Front' : 'View Back'}</span>
      </button>
    </div>
  );
}