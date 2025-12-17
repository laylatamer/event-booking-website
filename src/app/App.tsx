import { useState } from 'react';
import { EventTicket } from './components/EventTicket';

// Mock event data - in production, this would come from your database
const mockEvents = [
  {
    id: 1,
    eventName: 'Summer Music Festival 2025',
    eventDate: '25 JUL 2025',
    eventTime: '7:00 PM',
    venue: 'Downtown Arena',
  },
  {
    id: 2,
    eventName: 'Tech Conference 2025',
    eventDate: '15 AUG 2025',
    eventTime: '9:00 AM',
    venue: 'Convention Center',
  },
  {
    id: 3,
    eventName: 'Stand-Up Comedy Night',
    eventDate: '03 SEP 2025',
    eventTime: '8:30 PM',
    venue: 'Comedy Club',
  },
];

export default function App() {
  const [selectedEvent, setSelectedEvent] = useState(mockEvents[0]);
  const [userName, setUserName] = useState('JOHN DOE');

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black p-8">
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <h1 className="text-white text-5xl font-bold mb-4">EGZLY Ticket System</h1>
          <p className="text-gray-400">Your digital event tickets with style</p>
        </div>

        {/* Controls */}
        <div className="bg-gray-800/50 backdrop-blur-sm rounded-lg p-6 mb-8 border border-gray-700">
          <h2 className="text-white text-xl font-semibold mb-4">Ticket Configuration</h2>
          
          <div className="grid gap-6 md:grid-cols-2">
            {/* Event Selection */}
            <div>
              <label htmlFor="event" className="block text-gray-300 font-medium mb-2">
                Select Event
              </label>
              <select
                id="event"
                value={selectedEvent.id}
                onChange={(e) => {
                  const event = mockEvents.find(ev => ev.id === Number(e.target.value));
                  if (event) setSelectedEvent(event);
                }}
                className="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#ff5722]"
              >
                {mockEvents.map((event) => (
                  <option key={event.id} value={event.id}>
                    {event.eventName}
                  </option>
                ))}
              </select>
            </div>

            {/* User Name Input */}
            <div>
              <label htmlFor="userName" className="block text-gray-300 font-medium mb-2">
                Guest Name (as on checkout)
              </label>
              <input
                id="userName"
                type="text"
                value={userName}
                onChange={(e) => setUserName(e.target.value.toUpperCase())}
                placeholder="Enter your name"
                className="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#ff5722]"
              />
            </div>
          </div>

          {/* Event Details Display */}
          <div className="mt-6 pt-6 border-t border-gray-700">
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
              <div>
                <span className="text-gray-500">Date:</span>
                <span className="text-white ml-2 font-semibold">{selectedEvent.eventDate}</span>
              </div>
              <div>
                <span className="text-gray-500">Time:</span>
                <span className="text-white ml-2 font-semibold">{selectedEvent.eventTime}</span>
              </div>
              <div>
                <span className="text-gray-500">Venue:</span>
                <span className="text-white ml-2 font-semibold">{selectedEvent.venue}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Ticket Display */}
        <div className="bg-gray-800/30 backdrop-blur-sm rounded-lg p-8 border border-gray-700">
          <EventTicket
            eventName={selectedEvent.eventName}
            eventDate={selectedEvent.eventDate}
            userName={userName}
            eventTime={selectedEvent.eventTime}
            venue={selectedEvent.venue}
          />
        </div>

        {/* Info Footer */}
        <div className="mt-8 text-center text-gray-500 text-sm">
          <p>This is a preview of your event ticket.</p>
          <p>In production, data is dynamically fetched from your database.</p>
        </div>
      </div>
    </div>
  );
}
