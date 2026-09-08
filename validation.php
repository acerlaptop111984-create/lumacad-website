<?php

function validateRequired(string $value, string $label): ?string
{
    return trim($value) === '' ? "$label is required." : null;
}

function validateEmailFormat(string $value): ?string
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "Enter a valid email address.";
}

function validatePasswordStrength(string $value): ?string
{
    return strlen($value) < 6 ? "Password must be at least 6 characters." : null;
}

function validateIntRange(string $value, string $label, int $min, int $max): ?string
{
    $ok = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => $min, 'max_range' => $max],
    ]);
    return $ok !== false ? null : "$label must be between $min and $max.";
}

function validateRegistrationInput(array $post): array
{
    $full_name = trim($post['full_name'] ?? '');
    $email     = trim($post['email'] ?? '');
    $password  = trim($post['password'] ?? '');
    $confirm   = trim($post['confirm_password'] ?? '');

    $errors = array_filter([
        validateRequired($full_name, 'Full Name'),
        validateRequired($email, 'Email'),
        validateEmailFormat($email),
        validateRequired($password, 'Password'),
        validatePasswordStrength($password),
    ]);

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    return array_values($errors);
}
function validateBookingInput(array $post): array
{
    $service_type = trim($post['service_type'] ?? '');
    $weight       = trim($post['weight'] ?? '');
    $address      = trim($post['pickup_address'] ?? '');
    $payment      = trim($post['payment_method'] ?? '');

    $errors = array_filter([
        validateRequired($service_type, 'Service Type'),
        validateRequired($weight, 'Weight'),
        validateRequired($address, 'Pickup Address'),
        validateRequired($payment, 'Payment Method'),
    ]);

    if (!empty($weight) && !is_numeric($weight)) {
        $errors[] = "Weight must be a number.";
    }

    if (!empty($weight) && $weight <= 0) {
        $errors[] = "Weight must be greater than 0.";
    }

    return array_values($errors);
}

function validateProfileInput(array $post): array
{
    $full_name = trim($post['full_name'] ?? '');
    $contact   = trim($post['contact_number'] ?? '');

    $errors = array_filter([
        validateRequired($full_name, 'Full Name'),
    ]);

    if (!empty($contact)) {
        $contact_clean = preg_replace('/[^0-9]/', '', $contact);
        if (strlen($contact_clean) < 10 || strlen($contact_clean) > 11) {
            $errors[] = "Please enter a valid contact number.";
        }
    }

    return array_values($errors);
}
?>