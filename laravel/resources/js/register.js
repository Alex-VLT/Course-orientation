document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('is_club');
    const clubInputs = document.getElementById('club_inputs');

    // Safety check: if elements don't exist (e.g., on another page), stop execution.
    // We removed 'ppsInput' from this check since it was deleted from HTML.
    if (!checkbox || !clubInputs) return;

    function toggleInputs() {
        if (checkbox.checked) {
            // If checked: Show the license input field
            clubInputs.classList.remove('hidden');
        } else {
            // If unchecked: Hide the license input field
            clubInputs.classList.add('hidden');
        }
    }

    // Listen for state changes (user clicks the checkbox)
    checkbox.addEventListener('change', toggleInputs);

    // Run function on load to handle initial state 
    // (Crucial for preserving the view when Laravel returns validation errors with 'old' input)
    toggleInputs();
});