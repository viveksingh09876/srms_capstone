// Edit Student Function
function editStudent(id) {
    fetch(`get_student.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch student');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            // Populate form fields
            document.getElementById('editId').value = data.id || '';
            document.getElementById('editName').value = data.name || '';
            document.getElementById('editRollNumber').value = data.roll_number || '';
            document.getElementById('editEmail').value = data.email || '';
            document.getElementById('editCourse').value = data.course || '';
            
            // Handle grades: Convert JSON object to "Subject:Score\n" lines for textarea
            let gradesText = '';
            if (data.grades && typeof data.grades === 'object' && !Array.isArray(data.grades)) {
                for (const [subject, score] of Object.entries(data.grades)) {
                    gradesText += `${subject}:${score}\n`;
                }
            }
            document.getElementById('editGrades').value = gradesText.trim();
            
            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load student data. Please try again.');
        });
}

// Handle Edit Form Submission (Validation)
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const email = document.getElementById('editEmail').value;
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email.');
                return;
            }
            if (!confirm('Are you sure you want to update this student?')) {
                e.preventDefault();
                return;
            }
        });
    }

    // Similar for create form
    const createForm = document.querySelector('#createModal form');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            const email = this.querySelector('input[type="email"]').value;
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email.');
            }
        });
    }

    // Reset edit form on modal close
    document.getElementById('editModal').addEventListener('hidden.bs.modal', function () {
        this.querySelector('form').reset();
    });
});
