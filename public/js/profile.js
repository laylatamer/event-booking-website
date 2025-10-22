document.addEventListener('DOMContentLoaded', function () {

    // 1. Profile Picture Upload Logic
    const profileUpload = document.getElementById('profile-upload');
    const profilePic = document.getElementById('profile-pic');

    profileUpload.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            profilePic.src = URL.createObjectURL(file);
        }
    });

    // 2. Password Visibility Toggle Logic
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');

    passwordToggle.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '🙈';
    });

    // 3 & 4. Dynamic Country and State Dropdowns Logic
    const countrySelect = document.getElementById('country-select');
    const stateSelect = document.getElementById('state-select');

    async function populateCountries() {
        try {
            const response = await fetch('https://countriesnow.space/api/v0.1/countries/iso');
            const result = await response.json();
            if (result.error) throw new Error(result.msg);
            
            const countries = result.data;
            countries.sort((a, b) => a.name.localeCompare(b.name));
            
            let options = '<option value="">Select a Country</option>';
            countries.forEach(country => {
                options += `<option value="${country.name}">${country.name}</option>`;
            });
            countrySelect.innerHTML = options;
        } catch (error) {
            console.error('Error fetching countries:', error);
            countrySelect.innerHTML = '<option value="">Could not load countries</option>';
        }
    }
    
    async function populateStates(countryName) {
        stateSelect.disabled = true;
        stateSelect.innerHTML = '<option value="">Loading states...</option>';
        
        try {
            const response = await fetch('https://countriesnow.space/api/v0.1/countries/states', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ "country": countryName })
            });
            const result = await response.json();

            if (!result.error && result.data.states && result.data.states.length > 0) {
                let options = '<option value="">Select a State / Province</option>';
                result.data.states.sort((a, b) => a.name.localeCompare(b.name));
                result.data.states.forEach(state => {
                    options += `<option value="${state.name}">${state.name}</option>`;
                });
                stateSelect.innerHTML = options;
                stateSelect.disabled = false;
            } else {
                stateSelect.innerHTML = '<option value="">No states found</option>';
            }
        } catch (error) {
            console.error('Error fetching states:', error);
            stateSelect.innerHTML = '<option value="">Could not load states</option>';
        }
    }

    countrySelect.addEventListener('change', function () {
        const selectedCountry = this.value;
        if (selectedCountry) {
            populateStates(selectedCountry);
        } else {
            stateSelect.disabled = true;
            stateSelect.innerHTML = '<option value="">Select Country First</option>';
        }
    });

    // 5. Egyptian League Teams Dropdown Logic (Hardcoded for reliability)
    function populateTeams() {
        const teamSelect = document.getElementById('team-select');
        const egyptianTeams = [
            "Al Ahly SC", "Zamalek SC", "Pyramids FC", "Future FC",
            "Smouha SC", "Al Masry SC", "Ismaily SC", "Al Ittihad Alexandria",
            "Pharco FC", "Tala'ea El Gaish", "Ghazl El Mahalla", "Ceramica Cleopatra FC",
            "National Bank of Egypt SC", "Aswan SC", "El Dakhleya SC", "Haras El Hodoud"
        ];
        egyptianTeams.sort();
        let options = '<option value="">Select a Team</option>';
        egyptianTeams.forEach(team => {
            options += `<option value="${team}">${team}</option>`;
        });
        teamSelect.innerHTML = options;
    }

    // Initial population of all dropdowns
    populateCountries();
    populateTeams();
});