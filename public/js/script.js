// Create Rule
document.getElementById('createRuleForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const ruleName = document.getElementById('ruleName').value;
    const ruleString = document.getElementById('ruleString').value;

    fetch('http://localhost/rule_engine_project/api/rules.php?action=create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            rule_name: ruleName,
            rule_string: ruleString
        })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        loadRules();
    })
    .catch(error => alert('Error creating rule: ' + error));
});

// Modify Rule
document.getElementById('modifyRuleForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const ruleId = document.getElementById('ruleId').value; // Add input for rule ID
    const oldExpression = document.getElementById('oldExpression').value; // Add input for old expression
    const newExpression = document.getElementById('newExpression').value; // Add input for new expression

    console.log("Submitting:", { ruleId, oldExpression, newExpression }); // Debugging line

    fetch('http://localhost/rule_engine_project/api/rules.php?action=modify', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            rule_id: ruleId,
            old_expression: oldExpression,
            new_expression: newExpression
        })
    })
    .then(response => {
        console.log("Response:", response); // Debugging line
        return response.json();
    })
    .then(data => {
        console.log("Data:", data); // Debugging line
        alert(data.message);
        // Optionally, you can refresh the rules list here
        loadRules(); // Assuming you have a function to load the list of rules
    })
    .catch(error => {
        console.error('Error:', error); // Log the error
        alert('Error modifying rule: ' + error);
    });
});

// Delete Rule
document.getElementById('deleteRuleForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const ruleId = document.getElementById('ruleIdToDelete').value; // Use the correct ID

    fetch('http://localhost/rule_engine_project/api/rules.php?action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            rule_id: ruleId // Send Rule ID to delete
        })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message); // Show success or error message
        loadRules(); // Refresh the rules list
    })
    .catch(error => alert('Error deleting rule: ' + error));
});
// Create Attribute
document.getElementById('createAttributeForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const attributeName = document.getElementById('attributeName').value;
    const dataType = document.getElementById('dataType').value;

    fetch('http://localhost/rule_engine_project/api/attributes.php?action=add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            attribute_name: attributeName,
            data_type: dataType
        })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
    })
    .catch(error => alert('Error adding attribute: ' + error));
});
//Delete Attribute
// Ensure your script runs after the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function () {
    // Attach the event listener to the form
    document.getElementById('DeleteAttributeForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        // Ensure this matches the ID of your input field in HTML
        const attributeName = document.getElementById('attributeName').value; // Change this if necessary

        // Log to console to check if the attributeName is captured correctly
        console.log('Attempting to delete attribute:', attributeName);

        fetch('http://localhost/rule_engine_project/api/attributes.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                attribute_name: attributeName // Send Attribute Name to delete
            })
        })
        .then(response => {
            // Check if the response is OK (status 200-299)
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json(); // Parse JSON response
        })
        .then(data => {
            alert(data.message); // Show success or error message
            // Optionally, refresh the attributes list here
            // loadAttributes(); // Uncomment if you have this function
        })
        .catch(error => alert('Error deleting attribute: ' + error));
    });
});
// Combine Rules
// JavaScript to dynamically load rule checkboxes and handle form submission
document.addEventListener('DOMContentLoaded', function () {
    // Fetch existing rules and populate the checkboxes
    fetch('http://localhost/rule_engine_project/api/rules.php?action=get') // Replace with the actual API endpoint for fetching rules
        .then(response => response.json())
        .then(data => {
            const rulesCheckboxesDiv = document.getElementById('rulesCheckboxes');
            if (data.rules) {
                data.rules.forEach(rule => {
                    const checkboxDiv = document.createElement('div');
                    checkboxDiv.classList.add('form-check');

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.classList.add('form-check-input');
                    checkbox.value = rule.id;
                    checkbox.id = `rule_${rule.id}`;

                    const label = document.createElement('label');
                    label.classList.add('form-check-label');
                    label.htmlFor = `rule_${rule.id}`;
                    label.textContent = rule.rule_name;

                    checkboxDiv.appendChild(checkbox);
                    checkboxDiv.appendChild(label);
                    rulesCheckboxesDiv.appendChild(checkboxDiv);
                });
            }
        });

    // Handle form submission for combining rules
    document.getElementById('combineRulesForm').addEventListener('submit', function (event) {
        event.preventDefault();

        // Get the selected rule IDs
        const selectedRuleIds = Array.from(document.querySelectorAll('#rulesCheckboxes input:checked'))
            .map(checkbox => checkbox.value);
        const operator = document.getElementById('operator').value;

        if (selectedRuleIds.length === 0) {
            alert('Please select at least one rule to combine.');
            return;
        }

        // Send the selected rule IDs and operator to the server
        fetch('http://localhost/rule_engine_project/api/rules.php?action=combine', { // Replace with the actual PHP file path
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                rule_ids: selectedRuleIds,
                operator: operator
            })
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('combinedRuleResult');
            resultDiv.classList.remove('alert', 'alert-success', 'alert-danger');

            if (data.combined_ast) {
                // Display the combined AST result
                resultDiv.textContent = `Combined AST: ${JSON.stringify(data.combined_ast, null, 2)}`;
                resultDiv.classList.add('alert', 'alert-success');
            } else {
                // Display the error message
                resultDiv.textContent = `Error: ${data.message}`;
                resultDiv.classList.add('alert', 'alert-danger');
            }
        })
        .catch(error => {
            console.error('Error combining rules:', error);
            const resultDiv = document.getElementById('combinedRuleResult');
            resultDiv.textContent = 'Error: Could not combine the rules due to a server issue.';
            resultDiv.classList.add('alert', 'alert-danger');
        });
    });
});


// Add User Data
document.getElementById('createUserDataForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const userId = document.getElementById('userId').value;
    console.log(userId,"adduser")
    const age = document.getElementById('age').value;
    const department = document.getElementById('department').value;
    const salary = document.getElementById('salary').value;
    const experience = document.getElementById('experience').value;

    fetch('http://localhost/rule_engine_project/api/user_data.php?action=add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id: userId,
            age: age,
            department: department,
            salary: salary,
            experience: experience
        })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
    })
    .catch(error => alert('Error adding user data: ' + error));
});
// Evaluate rules
document.getElementById('evaluateForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const userId = document.getElementById('UserId').value; // Trim the user ID to avoid leading/trailing spaces
    console.log(userId,"Userid")
    fetch('http://localhost/rule_engine_project/api/rules.php?action=evaluate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id: userId, action: 'evaluate' }
            
        )
        
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json(); // Parse JSON response
    })
    .then(data => {
        console.log('API Response:', data); // Log the API response for debugging
        
        // Check for success or failure in the response
        if (data.message) {
            alert(data.message); // Alert the user if there's a message (e.g., user not found)
            return;
        }
        
        // Proceed if there are results
        if (data.results && Array.isArray(data.results)) {
            const evaluationResults = document.getElementById('evaluationResults');
            evaluationResults.innerHTML = ''; // Clear previous results

            data.results.forEach(result => {
                const div = document.createElement('div');
                div.className = 'alert alert-info';
                div.textContent = `Rule: ${result.rule_name}, Result: ${result.result}`;
                evaluationResults.appendChild(div);
            });
        } else {
            alert('No results found.');
        }
    })
    .catch(error => {
        console.error('Error evaluating rules:', error);
        alert('Error evaluating rules: ' + error.message);
    });
});

// Fetch Evaluation Results
async function getEvaluationResults() {
    try {
        const response = await fetch('http://localhost/rule_engine_project/api/evaluation_results.php?action=get');
        if (!response.ok) {
            throw new Error('Failed to fetch evaluation results');
        }
        const data = await response.json();

        // Display the fetched evaluation results
        const evaluationResultsList = document.getElementById('evaluationResultsList');
        evaluationResultsList.innerHTML = '';

        if (data.results && Array.isArray(data.results)) {
            data.results.forEach(result => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = `Rule ID: ${result.rule_id}, User ID: ${result.user_id}, Result: ${result.result}, Evaluated at: ${result.evaluated_at}`;
                evaluationResultsList.appendChild(li);
            });
        } else {
            alert('No results found.');
        }
    } catch (error) {
        console.error('Error fetching evaluation results:', error);
        alert('Error fetching evaluation results: ' + error.message);
    }
}
// Fetch Evaluation Results
document.getElementById('fetchEvaluationResults').addEventListener('click', async function() {
    try {
        const response = await fetch('http://localhost/rule_engine_project/api/evaluation_results.php?action=get');
        if (!response.ok) {
            throw new Error('Failed to fetch evaluation results');
        }
        const data = await response.json();

        // Display the fetched evaluation results
        const evaluationResultsList = document.getElementById('evaluationResultsList');
        evaluationResultsList.innerHTML = ''; // Clear previous results

        if (data && Array.isArray(data)) {
            data.forEach(result => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = `Rule ID: ${result.rule_id}, User ID: ${result.user_id}, Result: ${result.result}, Evaluated at: ${result.evaluated_at}`;
                evaluationResultsList.appendChild(li);
            });
        } else {
            alert('No results found.');
        }
    } catch (error) {
        console.error('Error fetching evaluation results:', error);
        alert('Error fetching evaluation results: ' + error.message);
    }
});

// Load Rules
function loadRules() {
    fetch('http://localhost/rule_engine_project/api/rules.php?action=get')
        .then(response => response.json())
        .then(data => {
            console.log('Response Data:', data); // Debugging line
            const rulesList = document.getElementById('rulesList');
            rulesList.innerHTML = '';

            // Check if data.rules exists and is an array
            if (Array.isArray(data.rules)) {
                data.rules.forEach(rule => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.textContent ='ID='+rule.id+'|| Rule Name: '+rule.rule_name + ' ||Rule String: ' + rule.rule_string;
                    rulesList.appendChild(li);
                });
            } else {
                alert('No rules found or error in fetching rules.');
            }
        })
        .catch(error => {
            console.error('Error loading rules:', error); // Debugging line
            alert('Error loading rules: ' + error);
        });
}
// Load rules when the page loads
document.addEventListener('DOMContentLoaded', loadRules);
