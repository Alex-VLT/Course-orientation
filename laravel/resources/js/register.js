document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('is_club');
    const clubInputs = document.getElementById('club_inputs');
    const ppsInput = document.getElementById('pps_input');

    // Security check: if elements don't exist (e.g., on another page), stop execution.
    if (!checkbox || !clubInputs || !ppsInput) return;

    function toggleInputs() {
        if (checkbox.checked) {
            // If checked: Show Club inputs, Hide PPS input
            clubInputs.classList.remove('hidden');
            ppsInput.classList.add('hidden');
        } else {
            // If unchecked: Hide Club inputs, Show PPS input
            clubInputs.classList.add('hidden');
            ppsInput.classList.remove('hidden');
        }
    }

    // Listen for state changes
    checkbox.addEventListener('change', toggleInputs);

    // Run function on load to handle initial state (e.g. preserving 'old' inputs after validation error)
    toggleInputs();
});