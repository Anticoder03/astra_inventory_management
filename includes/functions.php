<?php
// Function to display success message
function showSuccess($message) {
    return '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">' . $message . '</span>
    </div>';
}

// Function to display error message
function showError($message) {
    return '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">' . $message . '</span>
    </div>';
}

// Function to display warning message
function showWarning($message) {
    return '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">' . $message . '</span>
    </div>';
}

// Function to display info message
function showInfo($message) {
    return '<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">' . $message . '</span>
    </div>';
}

// Function to handle database errors
function handleDatabaseError($error) {
    $errorMessage = "Database Error: ";
    
    // Common database errors and their user-friendly messages
    $errorMessages = [
        "Duplicate entry" => "This record already exists in the database.",
        "Cannot delete or update a parent row" => "This record cannot be deleted because it is being used by other records.",
        "Data too long" => "The data you entered is too long for this field.",
        "Incorrect integer value" => "Please enter a valid number.",
        "Incorrect decimal value" => "Please enter a valid decimal number.",
        "Cannot add or update a child row" => "The referenced record does not exist.",
        "Access denied" => "You don't have permission to perform this action.",
        "Table doesn't exist" => "The required database table is missing.",
        "Unknown column" => "A required field is missing from the database."
    ];

    foreach ($errorMessages as $key => $message) {
        if (strpos($error, $key) !== false) {
            return $errorMessage . $message;
        }
    }

    return $errorMessage . "An unexpected error occurred. Please try again later.";
}

// Function to validate required fields
function validateRequired($value, $fieldName) {
    if (empty(trim($value))) {
        return "Please enter " . strtolower($fieldName) . ".";
    }
    return "";
}

// Function to validate numeric fields
function validateNumeric($value, $fieldName) {
    if (!is_numeric($value) || $value < 0) {
        return "Please enter a valid " . strtolower($fieldName) . ".";
    }
    return "";
}

// Function to validate SKU format
function validateSKU($sku) {
    if (!preg_match('/^[A-Z0-9-]+$/', $sku)) {
        return "SKU should only contain uppercase letters, numbers, and hyphens.";
    }
    return "";
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if a record exists
function recordExists($conn, $table, $field, $value, $excludeId = null) {
    $sql = "SELECT COUNT(*) as count FROM $table WHERE $field = ?";
    $params = [$value];
    $types = "s";

    if ($excludeId !== null) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
        $types .= "i";
    }

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row['count'] > 0;
    }
    return false;
}
?> 