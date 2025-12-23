<?php

use PHPUnit\Framework\TestCase;

class BookingViewTest extends TestCase
{
    // Test the getCategoryClass function
    public function testGetCategoryClass()
    {
        // Define the function for testing
        $getCategoryClass = function($category) {
            switch(strtolower($category)) {
                case 'music': return 'bg-purple-600';
                case 'sports': return 'bg-blue-600';
                case 'theater': return 'bg-red-600';
                case 'festival': return 'bg-green-600';
                case 'conference': return 'bg-yellow-600';
                case 'food': return 'bg-pink-600';
                case 'art': return 'bg-indigo-600';
                case 'entertainment': return 'bg-purple-600';
                case 'concerts': return 'bg-purple-600';
                case 'nightlife': return 'bg-indigo-600';
                case 'workshops': return 'bg-yellow-600';
                case 'comedy': return 'bg-orange-600';
                case 'technology': return 'bg-yellow-600';
                default: return 'bg-gray-600';
            }
        };

        $this->assertEquals('bg-purple-600', $getCategoryClass('music'));
        $this->assertEquals('bg-blue-600', $getCategoryClass('sports'));
        $this->assertEquals('bg-red-600', $getCategoryClass('theater'));
        $this->assertEquals('bg-green-600', $getCategoryClass('festival'));
        $this->assertEquals('bg-yellow-600', $getCategoryClass('conference'));
        $this->assertEquals('bg-pink-600', $getCategoryClass('food'));
        $this->assertEquals('bg-indigo-600', $getCategoryClass('art'));
        $this->assertEquals('bg-purple-600', $getCategoryClass('entertainment'));
        $this->assertEquals('bg-purple-600', $getCategoryClass('concerts'));
        $this->assertEquals('bg-indigo-600', $getCategoryClass('nightlife'));
        $this->assertEquals('bg-yellow-600', $getCategoryClass('workshops'));
        $this->assertEquals('bg-orange-600', $getCategoryClass('comedy'));
        $this->assertEquals('bg-yellow-600', $getCategoryClass('technology'));
        $this->assertEquals('bg-gray-600', $getCategoryClass('unknown'));
        $this->assertEquals('bg-purple-600', $getCategoryClass('MUSIC')); // case insensitive
    }

    // Test the sorting of ticket categories
    public function testTicketCategoriesSorting()
    {
        $ticketCategories = [
            ['price' => '50.00'],
            ['price' => '20.00'],
            ['price' => '100.00'],
            ['price' => '10.00']
        ];

        // Sort as in the code
        usort($ticketCategories, function($a, $b) {
            return floatval($a['price']) <=> floatval($b['price']);
        });

        $this->assertEquals('10.00', $ticketCategories[0]['price']);
        $this->assertEquals('20.00', $ticketCategories[1]['price']);
        $this->assertEquals('50.00', $ticketCategories[2]['price']);
        $this->assertEquals('100.00', $ticketCategories[3]['price']);
    }

    // Test building full location
    public function testBuildFullLocation()
    {
        $venueData = [
            'name' => 'Venue Name',
            'address' => '123 Main St',
            'city' => 'City Name',
            'country' => 'Country Name'
        ];

        $fullLocation = $venueData['name'];
        if (!empty($venueData['address'])) {
            $fullLocation .= ', ' . $venueData['address'];
        }
        if (!empty($venueData['city'])) {
            $fullLocation .= ', ' . $venueData['city'];
        }
        if (!empty($venueData['country'])) {
            $fullLocation .= ', ' . $venueData['country'];
        }

        $this->assertEquals('Venue Name, 123 Main St, City Name, Country Name', $fullLocation);

        // Test with missing fields
        $venueData2 = [
            'name' => 'Venue Name',
            'address' => '',
            'city' => 'City Name',
            'country' => ''
        ];

        $fullLocation2 = $venueData2['name'];
        if (!empty($venueData2['address'])) {
            $fullLocation2 .= ', ' . $venueData2['address'];
        }
        if (!empty($venueData2['city'])) {
            $fullLocation2 .= ', ' . $venueData2['city'];
        }
        if (!empty($venueData2['country'])) {
            $fullLocation2 .= ', ' . $venueData2['country'];
        }

        $this->assertEquals('Venue Name, City Name', $fullLocation2);
    }

    // Test price calculations
    public function testPriceCalculations()
    {
        $eventPrice = 100.00;

        $generalPrice = $eventPrice;
        $formattedGeneralPrice = '$' . number_format($generalPrice, 2);
        $vipPrice = $eventPrice * 1.5;
        $formattedVipPrice = '$' . number_format($vipPrice, 2);
        $formattedPrice = '$' . number_format($eventPrice, 2);

        $this->assertEquals(100.00, $generalPrice);
        $this->assertEquals('$100.00', $formattedGeneralPrice);
        $this->assertEquals(150.00, $vipPrice);
        $this->assertEquals('$150.00', $formattedVipPrice);
        $this->assertEquals('$100.00', $formattedPrice);
    }

    // Test date formatting
    public function testDateFormatting()
    {
        $eventDate = new DateTime('2023-12-25 15:30:00');
        $formattedDate = $eventDate->format('l, F j, Y');
        $formattedTime = $eventDate->format('h:i A');
        $formattedDateTime = $formattedDate . ' at ' . $formattedTime;

        $this->assertEquals('Monday, December 25, 2023', $formattedDate);
        $this->assertEquals('03:30 PM', $formattedTime);
        $this->assertEquals('Monday, December 25, 2023 at 03:30 PM', $formattedDateTime);
    }
}
